<?php

namespace Modules\Auth\Repositories;

use Modules\Auth\Models\UserPermission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserPermissionRepository
{
    protected UserPermission $model;

    public function __construct(UserPermission $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new permission.
     */
    public function create(array $data): UserPermission
    {
        return $this->model->create($data);
    }

    /**
     * Update a permission.
     */
    public function update(int $id, array $data): UserPermission
    {
        $permission = $this->model->findOrFail($id);
        $permission->update($data);
        return $permission->fresh();
    }

    /**
     * Delete a permission.
     */
    public function delete(int $id): bool
    {
        return $this->model->destroy($id) > 0;
    }

    /**
     * Find permission by ID.
     */
    public function find(int $id): ?UserPermission
    {
        return $this->model->find($id);
    }

    /**
     * Get permissions for a team user.
     */
    public function getByTeamUserId(int $teamUserId): Collection
    {
        return $this->model->where('subject_type', 'Modules\\Auth\\Models\\BrokerTeamUser')
                          ->where('subject_id', $teamUserId)
                          ->active()
                          ->orderBy('permission_type')
                          ->orderBy('action')
                          ->get();
    }

    /**
     * Get permissions by type for a team user.
     */
    public function getByTeamUserAndType(int $teamUserId, string $type): Collection
    {
        return $this->model->where('subject_type', 'Modules\\Auth\\Models\\BrokerTeamUser')
                          ->where('subject_id', $teamUserId)
                          ->ofType($type)
                          ->active()
                          ->get();
    }

    /**
     * Get permissions by action for a team user.
     */
    public function getByTeamUserAndAction(int $teamUserId, string $action): Collection
    {
        return $this->model->where('subject_type', 'Modules\\Auth\\Models\\BrokerTeamUser')
                          ->where('subject_id', $teamUserId)
                          ->withAction($action)
                          ->active()
                          ->get();
    }

    /**
     * Get permissions for specific resource.
     */
    public function getForResource(int $teamUserId, string $type, $resourceId = null, $resourceValue = null): Collection
    {
        return $this->model->where('subject_type', 'Modules\\Auth\\Models\\BrokerTeamUser')
                          ->where('subject_id', $teamUserId)
                          ->forResource($type, $resourceId, $resourceValue)
                          ->active()
                          ->get();
    }

    /**
     * Check if user has specific permission.
     */
    public function hasPermission(int $teamUserId, string $type, string $action, $resourceId = null, $resourceValue = null): bool
    {
        return $this->model->where('subject_type', 'Modules\\Auth\\Models\\BrokerTeamUser')
                          ->where('subject_id', $teamUserId)
                          ->forResource($type, $resourceId, $resourceValue)
                          ->withAction($action)
                          ->active()
                          ->exists();
    }

    /**
     * Check if user can perform action on resource.
     */
    public function canPerformAction(int $teamUserId, string $type, string $action, $resourceId = null, $resourceValue = null): bool
    {
        $permissions = $this->model->where('subject_type', 'Modules\\Auth\\Models\\BrokerTeamUser')
                                  ->where('subject_id', $teamUserId)
                                  ->forResource($type, $resourceId, $resourceValue)
                                  ->active()
                                  ->get();

        foreach ($permissions as $permission) {
            if ($permission->allowsAction($action)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all permissions for a team user with filters.
     */
    public function getWithFilters(int $teamUserId, array $filters = []): Collection
    {
        $query = $this->model->where('subject_type', 'Modules\\Auth\\Models\\BrokerTeamUser')
                            ->where('subject_id', $teamUserId);

        if (isset($filters['permission_type'])) {
            $query->ofType($filters['permission_type']);
        }

        if (isset($filters['action'])) {
            $query->withAction($filters['action']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['resource_id'])) {
            $query->where('resource_id', $filters['resource_id']);
        }

        if (isset($filters['resource_value'])) {
            $query->where('resource_value', $filters['resource_value']);
        }

        return $query->orderBy('permission_type')->orderBy('action')->get();
    }

    /**
     * Get paginated permissions for a team user.
     */
    public function paginate(int $teamUserId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->model->where('subject_type', 'Modules\\Auth\\Models\\BrokerTeamUser')
                          ->where('subject_id', $teamUserId)
                          ->orderBy('permission_type')
                          ->orderBy('action')
                          ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Bulk create permissions.
     */
    public function createMany(array $permissions): bool
    {
        return $this->model->insert($permissions);
    }

    /**
     * Delete all permissions for a team user.
     */
    public function deleteByTeamUserId(int $teamUserId): int
    {
        return $this->model->where('subject_type', 'Modules\\Auth\\Models\\BrokerTeamUser')
                          ->where('subject_id', $teamUserId)
                          ->delete();
    }

    /**
     * Delete permissions by type for a team user.
     */
    public function deleteByType(int $teamUserId, string $type): int
    {
        return $this->model->where('subject_type', 'Modules\\Auth\\Models\\BrokerTeamUser')
                          ->where('subject_id', $teamUserId)
                          ->ofType($type)
                          ->delete();
    }

    /**
     * Toggle permission active status.
     */
    public function toggleActive(int $id): UserPermission
    {
        $permission = $this->model->findOrFail($id);
        $permission->update(['is_active' => !$permission->is_active]);
        return $permission->fresh();
    }

    /**
     * Get permission statistics for a team user.
     */
    public function getStats(int $teamUserId): array
    {
        $stats = $this->model->where('subject_type', 'Modules\\Auth\\Models\\BrokerTeamUser')
                            ->where('subject_id', $teamUserId)
                            ->selectRaw('
                                permission_type,
                                action,
                                COUNT(*) as count,
                                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_count
                            ')
                            ->groupBy('permission_type', 'action')
                            ->get();

        return [
            'total_permissions' => $stats->sum('count'),
            'active_permissions' => $stats->sum('active_count'),
            'by_type' => $stats->groupBy('permission_type'),
            'by_action' => $stats->groupBy('action'),
        ];
    }

    /**
     * Get permissions for any subject type (polymorphic).
     */
    public function getBySubject(string $subjectType, int $subjectId): Collection
    {
        return $this->model->where('subject_type', $subjectType)
                          ->where('subject_id', $subjectId)
                          ->active()
                          ->orderBy('permission_type')
                          ->orderBy('action')
                          ->get();
    }

    /**
     * Check if subject has specific permission (polymorphic).
     */
    public function hasSubjectPermission(string $subjectType, int $subjectId, string $type, string $action, $resourceId = null, $resourceValue = null): bool
    {
        return $this->model->where('subject_type', $subjectType)
                          ->where('subject_id', $subjectId)
                          ->forResource($type, $resourceId, $resourceValue)
                          ->withAction($action)
                          ->active()
                          ->exists();
    }

    /**
     * Delete all permissions for a subject (polymorphic).
     */
    public function deleteBySubject(string $subjectType, int $subjectId): int
    {
        return $this->model->where('subject_type', $subjectType)
                          ->where('subject_id', $subjectId)
                          ->delete();
    }
}