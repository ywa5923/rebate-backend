<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;


trait RepositoryTrait
{
    /**
     * Get all models
     * @return Collection
     */
    function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Create a new model instance
     * @param array $data
     * @return Model The created model instance (type depends on $this->model)
     */
    function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update a model by ID
     * @param array $data
     * @param int|string $id
     * @return Model|null The updated model instance (type depends on $this->model)
     */
    function update(array $data, $id): ?Model
    {
        $model = $this->model->find($id);
        if (!$model) {
            return null;
        }
        $model->update($data);
        return $model->fresh();
    }

    /**
     * Delete a model by ID
     * @param int|string $id
     * @return bool
     */
    function delete($id): bool
    {
        $model = $this->model->find($id);
        if (!$model) {
            return false;
        }
        return $model->delete();
    }

    /**
     * Find a model by ID
     * @param int|string $id
     * @return Model|null The found model instance (type depends on $this->model)
     */
    function find($id): ?Model
    {
        return $this->model->find($id);
    }
}

