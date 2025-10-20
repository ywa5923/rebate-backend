<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\UserPermission;
use Modules\Auth\Repositories\UserPermissionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class UserPermissionService
{
    protected UserPermissionRepository $repository;

    public function __construct(UserPermissionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new permission for a team user.
     */
    public function createPermission(array $data): UserPermission
    {
        try {
            return $this->repository->create($data);
        } catch (\Exception $e) {
            Log::error('Failed to create permission', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update a permission.
     */
    public function updatePermission(int $id, array $data): UserPermission
    {
        try {
            return $this->repository->update($id, $data);
        } catch (\Exception $e) {
            Log::error('Failed to update permission', [
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete a permission.
     */
    public function deletePermission(int $id): bool
    {
        try {
            return $this->repository->delete($id);
        } catch (\Exception $e) {
            Log::error('Failed to delete permission', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get all permissions for a team user.
     */
    public function getTeamUserPermissions(int $teamUserId): Collection
    {
        return $this->repository->getByTeamUserId($teamUserId);
    }

    /**
     * Get permissions by type for a team user.
     */
    public function getPermissionsByType(int $teamUserId, string $type): Collection
    {
        return $this->repository->getByTeamUserAndType($teamUserId, $type);
    }

    /**
     * Get permissions by action for a team user.
     */
    public function getPermissionsByAction(int $teamUserId, string $action): Collection
    {
        return $this->repository->getByTeamUserAndAction($teamUserId, $action);
    }

    /**
     * Get permissions for specific resource.
     */
    public function getResourcePermissions(int $teamUserId, string $type, $resourceId = null, $resourceValue = null): Collection
    {
        return $this->repository->getForResource($teamUserId, $type, $resourceId, $resourceValue);
    }

    /**
     * Check if user has specific permission.
     */
    public function hasPermission(int $teamUserId, string $type, string $action, $resourceId = null, $resourceValue = null): bool
    {
        return $this->repository->hasPermission($teamUserId, $type, $action, $resourceId, $resourceValue);
    }

    /**
     * Check if user can perform action on resource.
     */
    public function canPerformAction(int $teamUserId, string $type, string $action, $resourceId = null, $resourceValue = null): bool
    {
        return $this->repository->canPerformAction($teamUserId, $type, $action, $resourceId, $resourceValue);
    }

    /**
     * Get filtered permissions for a team user.
     */
    public function getFilteredPermissions(int $teamUserId, array $filters = []): Collection
    {
        return $this->repository->getWithFilters($teamUserId, $filters);
    }

    /**
     * Get paginated permissions for a team user.
     */
    public function getPaginatedPermissions(int $teamUserId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->repository->paginate($teamUserId, $perPage, $page);
    }

    /**
     * Create multiple permissions at once.
     */
    public function createMultiplePermissions(array $permissions): bool
    {
        try {
            DB::beginTransaction();
            
            $result = $this->repository->createMany($permissions);
            
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create multiple permissions', [
                'permissions' => $permissions,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete all permissions for a team user.
     */
    public function deleteAllTeamUserPermissions(int $teamUserId): int
    {
        try {
            return $this->repository->deleteByTeamUserId($teamUserId);
        } catch (\Exception $e) {
            Log::error('Failed to delete all team user permissions', [
                'team_user_id' => $teamUserId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete permissions by type for a team user.
     */
    public function deletePermissionsByType(int $teamUserId, string $type): int
    {
        try {
            return $this->repository->deleteByType($teamUserId, $type);
        } catch (\Exception $e) {
            Log::error('Failed to delete permissions by type', [
                'team_user_id' => $teamUserId,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Toggle permission active status.
     */
    public function togglePermissionActive(int $id): UserPermission
    {
        try {
            return $this->repository->toggleActive($id);
        } catch (\Exception $e) {
            Log::error('Failed to toggle permission active status', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get permission statistics for a team user.
     */
    public function getPermissionStats(int $teamUserId): array
    {
        return $this->repository->getStats($teamUserId);
    }

    /**
     * Assign broker permission to team user.
     */
    public function assignBrokerPermission(int $teamUserId, int $brokerId, string $action = 'view'): UserPermission
    {
        return $this->createPermission([
            'subject_type' => 'Modules\\Auth\\Models\\BrokerTeamUser',
            'subject_id' => $teamUserId,
            'permission_type' => 'broker',
            'resource_id' => $brokerId,
            'action' => $action,
        ]);
    }

    /**
     * Assign country permission to team user.
     */
    public function assignCountryPermission(int $teamUserId, string $country, string $action = 'view'): UserPermission
    {
        return $this->createPermission([
            'subject_type' => 'Modules\\Auth\\Models\\BrokerTeamUser',
            'subject_id' => $teamUserId,
            'permission_type' => 'country',
            'resource_value' => $country,
            'action' => $action,
        ]);
    }

    /**
     * Assign zone permission to team user.
     */
    public function assignZonePermission(int $teamUserId, string $zone, string $action = 'view'): UserPermission
    {
        return $this->createPermission([
            'subject_type' => 'Modules\\Auth\\Models\\BrokerTeamUser',
            'subject_id' => $teamUserId,
            'permission_type' => 'zone',
            'resource_value' => $zone,
            'action' => $action,
        ]);
    }

    /**
     * Assign broker type permission to team user.
     */
    public function assignBrokerTypePermission(int $teamUserId, string $brokerType, string $action = 'view'):UserPermission
    {
        return $this->createPermission([
            'subject_type' => 'Modules\\Auth\\Models\\BrokerTeamUser',
            'subject_id' => $teamUserId,
            'permission_type' => 'broker_type',
            'resource_value' => $brokerType,
            'action' => $action,
        ]);
    }

    /**
     * Check if user can access specific broker.
     */
    public function canAccessBroker(int $teamUserId, int $brokerId): bool
    {
        return $this->canPerformAction($teamUserId, 'broker', 'view', $brokerId);
    }

    /**
     * Check if user can edit specific broker.
     */
    public function canEditBroker(int $teamUserId, int $brokerId): bool
    {
        return $this->canPerformAction($teamUserId, 'broker', 'edit', $brokerId);
    }

    /**
     * Check if user can manage specific broker.
     */
    public function canManageBroker(int $teamUserId, int $brokerId): bool
    {
        return $this->canPerformAction($teamUserId, 'broker', 'manage', $brokerId);
    }

    /**
     * Check if user can access brokers from specific country.
     */
    public function canAccessCountry(int $teamUserId, string $country): bool
    {
        return $this->canPerformAction($teamUserId, 'country', 'view', null, $country);
    }

    /**
     * Check if user can access brokers from specific zone.
     */
    public function canAccessZone(int $teamUserId, string $zone): bool
    {
        return $this->canPerformAction($teamUserId, 'zone', 'view', null, $zone);
    }

    /**
     * Get available permission types.
     */
    public function getAvailablePermissionTypes(): array
    {
        return ['broker', 'country', 'zone', 'broker_type'];
    }

    /**
     * Get available actions.
     */
    public function getAvailableActions(): array
    {
        return ['view', 'edit', 'delete', 'manage'];
    }
}
