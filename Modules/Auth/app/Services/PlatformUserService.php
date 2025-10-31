<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\PlatformUser;
use Modules\Auth\Repositories\PlatformUserRepository;
use Illuminate\Support\Facades\Log;

class PlatformUserService
{
    protected PlatformUserRepository $repository;

    public function __construct(PlatformUserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new platform user
     */
    public function create(array $data): PlatformUser
    {
        try {
            $user = $this->repository->create($data);
            
            Log::info('Platform user created', ['user_id' => $user->id]);
            
            return $user;
        } catch (\Exception $e) {
            Log::error('PlatformUserService create error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a platform user
     */
    public function update(array $data): PlatformUser|null
    {
        try {
            $user = $this->repository->update($data);
            
            if ($user) {
                Log::info('Platform user updated', ['user_id' => $data['id']]);
            }
            
            return $user;
        } catch (\Exception $e) {
            Log::error('PlatformUserService update error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a platform user
     */
    public function delete(int $id): bool
    {
        try {
            $deleted = $this->repository->deleteById($id);
            
            if ($deleted) {
                Log::info('Platform user deleted', ['user_id' => $id]);
            }
            
            return $deleted;
        } catch (\Exception $e) {
            Log::error('PlatformUserService delete error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get platform user by ID
     */
    public function getById(int $id): ?PlatformUser
    {
        return $this->repository->find($id);
    }

    /**
     * Get all platform users with optional filters and pagination
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
     * Toggle active status of a platform user
     */
    public function toggleActiveStatus(int $id): ?PlatformUser
    {
        $user = $this->repository->find($id);
        
        if (!$user) {
            return null;
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return $user;
    }
}