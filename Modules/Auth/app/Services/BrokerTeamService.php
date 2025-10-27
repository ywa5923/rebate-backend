<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\BrokerTeam;
use Modules\Auth\Models\BrokerTeamUser;
use Modules\Auth\Repositories\BrokerTeamRepository;
use Modules\Auth\Repositories\BrokerTeamUserRepository;
use Modules\Brokers\Models\Broker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class BrokerTeamService
{
    protected BrokerTeamRepository $teamRepository;
    protected BrokerTeamUserRepository $userRepository;

    public function __construct(
        BrokerTeamRepository $teamRepository,
        BrokerTeamUserRepository $userRepository
    ) {
        $this->teamRepository = $teamRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Create a new broker team.
     */
    public function createTeam(array $data): BrokerTeam
    {
        return $this->teamRepository->create($data);
    }

    /**
     * Update a broker team.
     */
    public function updateTeam(array $data): BrokerTeam
    {
        return $this->teamRepository->update($data);
    }

    /**
     * Get team by ID.
     */
    public function getTeam(int $id): ?BrokerTeam
    {
        return $this->teamRepository->findByIdWithRelations($id);
    }

    /**
     * Get teams by broker ID.
     */
    public function getTeamsByBroker(int $brokerId): Collection
    {
        return $this->teamRepository->getByBrokerId($brokerId);
    }

    /**
     * Create a team user.
     */
    public function createTeamUser(array $data): BrokerTeamUser
    {
       
        return $this->userRepository->create($data);
    }

    

    /**
     * Get available roles.
     */
    public function getAvailableRoles(): array
    {
        return [
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'member' => 'Member',
        ];
    }

    /**
     * Get available permissions.
     */
    public function getAvailablePermissions(): array
    {
        return [
            'broker.view' => 'View broker data',
            'broker.edit' => 'Edit broker data',
            'broker.delete' => 'Delete broker data',
            'team.manage' => 'Manage team members',
            'team.invite' => 'Invite team members',
            'team.remove' => 'Remove team members',
            'reports.view' => 'View reports',
            'settings.manage' => 'Manage settings',
            '*' => 'All permissions',
        ];
    }
}
