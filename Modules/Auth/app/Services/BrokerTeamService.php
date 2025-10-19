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
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->create($data);
    }

    /**
     * Update a team user.
     */
    public function updateTeamUser(array $data): BrokerTeamUser
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->update($data);
    }

    /**
     * Get team user by email.
     */
    public function getTeamUserByEmail(string $email): ?BrokerTeamUser
    {
        return $this->userRepository->findByEmailWithRelations($email);
    }

    /**
     * Get team users by team ID.
     */
    public function getTeamUsers(int $teamId): Collection
    {
        return $this->userRepository->getByTeamId($teamId);
    }

    /**
     * Get team users by broker ID.
     */
    public function getBrokerTeamUsers(int $brokerId): Collection
    {
        return $this->userRepository->getByBrokerId($brokerId);
    }

    /**
     * Authenticate team user.
     */
    public function authenticateTeamUser(string $email, string $password): ?BrokerTeamUser
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$user->is_active) {
            return null;
        }

        if (!$user->password || !Hash::check($password, $user->password)) {
            return null;
        }

        // Update last login
        $this->userRepository->updateLastLogin($user->id);

        return $user;
    }

    /**
     * Check if user has permission.
     */
    public function userHasPermission(BrokerTeamUser $user, string $permission): bool
    {
        return $user->hasPermission($permission);
    }

    /**
     * Add permission to user.
     */
    public function addUserPermission(int $userId, string $permission): void
    {
        $user = $this->userRepository->findById($userId);
        if ($user) {
            $user->addPermission($permission);
        }
    }

    /**
     * Remove permission from user.
     */
    public function removeUserPermission(int $userId, string $permission): void
    {
        $user = $this->userRepository->findById($userId);
        if ($user) {
            $user->removePermission($permission);
        }
    }

    /**
     * Add permission to team.
     */
    public function addTeamPermission(int $teamId, string $permission): void
    {
        $team = $this->teamRepository->findById($teamId);
        if ($team) {
            $team->addPermission($permission);
        }
    }

    /**
     * Remove permission from team.
     */
    public function removeTeamPermission(int $teamId, string $permission): void
    {
        $team = $this->teamRepository->findById($teamId);
        if ($team) {
            $team->removePermission($permission);
        }
    }

    /**
     * Delete team user.
     */
    public function deleteTeamUser(int $userId): bool
    {
        return $this->userRepository->delete($userId);
    }

    /**
     * Delete team.
     */
    public function deleteTeam(int $teamId): bool
    {
        return $this->teamRepository->delete($teamId);
    }

    /**
     * Get team statistics.
     */
    public function getTeamStats(?int $brokerId = null): array
    {
        return [
            'teams' => $this->teamRepository->getStats($brokerId),
            'users' => $this->userRepository->getStats($brokerId),
        ];
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
