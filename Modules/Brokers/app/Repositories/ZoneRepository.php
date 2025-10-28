<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\Zone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ZoneRepository
{
    /**
     * Get all zones
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Zone::with(['countries', 'brokers'])->get();
    }

    /**
     * Get paginated zones with optional filters
     *
     * @param int $perPage
     * @param string $orderBy
     * @param string $orderDirection
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginated(
        int $perPage = 15,
        string $orderBy = 'id',
        string $orderDirection = 'asc',
        array $filters = []
    ): LengthAwarePaginator {
        $query = Zone::query();

        // Apply filters
        if (!empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (!empty($filters['zone_code'])) {
            $query->where('zone_code', 'like', "%{$filters['zone_code']}%");
        }

        if (!empty($filters['description'])) {
            $query->where('description', 'like', "%{$filters['description']}%");
        }

        // Apply ordering
        $query->orderBy($orderBy, $orderDirection);

        // Load relationships
        $query->with(['countries', 'brokers']);

        return $query->paginate($perPage);
    }

    /**
     * Find zone by ID
     *
     * @param int $id
     * @return Zone|null
     */
    public function findById(int $id): ?Zone
    {
        return Zone::with(['countries', 'brokers'])->find($id);
    }

    /**
     * Find zone by ID or fail
     *
     * @param int $id
     * @return Zone
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByIdOrFail(int $id): Zone
    {
        return Zone::with(['countries', 'brokers'])->findOrFail($id);
    }

    /**
     * Find zone by zone_code
     *
     * @param string $zoneCode
     * @return Zone|null
     */
    public function findByZoneCode(string $zoneCode): ?Zone
    {
        return Zone::where('zone_code', $zoneCode)->first();
    }

    /**
     * Create a new zone
     *
     * @param array $data
     * @return Zone
     */
    public function create(array $data): Zone
    {
        return Zone::create($data);
    }

    /**
     * Update a zone
     *
     * @param Zone $zone
     * @param array $data
     * @return bool
     */
    public function update(Zone $zone, array $data): bool
    {
        return $zone->update($data);
    }

    /**
     * Delete a zone
     *
     * @param Zone $zone
     * @return bool|null
     * @throws \Exception
     */
    public function delete(Zone $zone): ?bool
    {
        return $zone->delete();
    }

    /**
     * Check if zone_code exists
     *
     * @param string $zoneCode
     * @param int|null $excludeId
     * @return bool
     */
    public function zoneCodeExists(string $zoneCode, ?int $excludeId = null): bool
    {
        $query = Zone::where('zone_code', $zoneCode);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Get zones count
     *
     * @return int
     */
    public function count(): int
    {
        return Zone::count();
    }
}

