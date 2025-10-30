<?php

namespace Modules\Auth\Repositories;

use Modules\Auth\Models\PlatformUser;

class PlatformUserRepository
{
    protected PlatformUser $model;

    public function __construct(PlatformUser $model)
    {
        $this->model = $model;
    }

    public function create(array $data): PlatformUser
    {
        return $this->model->create($data);
    }

    public function update(array $data): PlatformUser|null
    {
        $user = $this->model->findOrFail($data['id']);
        $updated = $user->update($data);
        
        if (!$updated) {
            return null;
        }
        
        return $user->fresh();
    }

    public function delete(int $id): bool
    {
        $user = $this->model->find($id);
        
        if (!$user) {
            return false;
        }
        
        return $user->delete();
    }

    public function find(int $id): ?PlatformUser
    {
        return $this->model->find($id);
    }

    public function getAll(array $filters = [], string $orderBy = 'id', string $orderDirection = 'asc')
    {
        $query = $this->model->newQuery();

        // Apply filters
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        

        // Apply ordering
        $query->orderBy($orderBy, $orderDirection);

        return $query;
    }

    public function deleteById(int $id): bool
    {
        return $this->model->destroy($id) > 0;
    }
}