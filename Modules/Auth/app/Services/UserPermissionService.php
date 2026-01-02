<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\UserPermission;
use Modules\Auth\Repositories\UserPermissionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Models\PlatformUser;
use Modules\Auth\Forms\UserPermissionForm;
use Modules\Brokers\Models\Broker;
use Modules\Translations\Models\Country;
use Modules\Brokers\Models\Zone;
use Modules\Auth\Enums\AuthPermission;
use Modules\Auth\Enums\AuthAction;



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
    public function createPermission(array $data,AuthPermission $permissionType,AuthAction $actionType): UserPermission
    {
        $data['permission_type'] =  $permissionType->value;
        $data['action'] = $actionType->value;
       
        try {
            if($permissionType == AuthPermission::BROKER && !isset($data['resource_value'])) {
                $broker = Broker::with(['dynamicOptionsValues' => function ($q) {
                    $q->where('option_slug', 'trading_name')->latest('id')->limit(1);
                }])->find($data['resource_id']);
                
                $tradingName = optional($broker?->dynamicOptionsValues->first())->value;

                $data['resource_value'] = $tradingName;
            } else{
                //resurce model return specific class like Country, Zone, Broker, SEO, Translator
                //for broker prmission type  the resource value is the trading name
                //for country,  seo, translator the resource value is Country::name
                //for zone the resource value is Zone::name
                $model = $permissionType->resourceModel();
                $resource = $model::find($data['resource_id']);
                $data['resource_value'] = $resource->name;
                
            }
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
     * Get available permission types.
     */
    public function getAvailablePermissionTypes(): array
    {
        return [ 'country', 'zone', 'broker','seo','translator'];
    }

    /**
     * Get available actions.
     */
    public function getAvailableActions(): array
    {
        return ['view', 'edit', 'delete', 'manage'];
    }

    /**
     * Get all permissions with optional filters and pagination
     */
    public function getAll(array $filters = [], string $orderBy = 'id', string $orderDirection = 'asc', int $perPage = 15)
    {
        $query = $this->repository->getAll($filters, $orderBy, $orderDirection);
        
        if ($perPage > 0) {
            return $query->paginate($perPage);
        }
        
        return $query->get();
    }

    /**
     * Get permission by ID
     */
    public function getById(int $id): ?UserPermission
    {
        return $this->repository->find($id);
    }

    /**
     * Toggle active status of a permission
     */
    public function toggleActiveStatus(int $id): UserPermission
    {
        $permission = $this->repository->find($id);
        
        if (!$permission) {
            throw new \Exception('Permission not found');
        }

        $permission->is_active = !$permission->is_active;
        $permission->save();

      

        return $permission;
    }

    public function getOrderedBrokersList(): array
    {
        return $this->repository->getOrderedBrokersList();
    }
}
