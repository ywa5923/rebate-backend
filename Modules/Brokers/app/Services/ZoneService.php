<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\ZoneRepository;
use Modules\Brokers\Models\Zone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ZoneService
{
    public function __construct(
        protected ZoneRepository $zoneRepository
    ) {
    }

    /**
     * Get all zones
     *
     * @return Collection
     */
    public function getAllZones(): Collection
    {
        return $this->zoneRepository->getAll();
    }

    /**
     * Get paginated zones with filters
     *
     * @param int $perPage
     * @param string $orderBy
     * @param string $orderDirection
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getZoneList(
        int $perPage = 15,
        string $orderBy = 'id',
        string $orderDirection = 'asc',
        array $filters = []
    ): LengthAwarePaginator {
        return $this->zoneRepository->getPaginated($perPage, $orderBy, $orderDirection, $filters);
    }

    /**
     * Get zone by ID
     *
     * @param int $id
     * @return Zone
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getZoneById(int $id): Zone
    {
        return $this->zoneRepository->findByIdOrFail($id);
    }

    /**
     * Create a new zone
     *
     * @param array $data
     * @return Zone
     */
    public function createZone(array $data): Zone
    {
        return $this->zoneRepository->create($data);
    }

    /**
     * Update a zone
     *
     * @param int $id
     * @param array $data
     * @return Zone
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateZone(int $id, array $data): Zone
    {
        $zone = $this->zoneRepository->findByIdOrFail($id);
        $this->zoneRepository->update($zone, $data);
        
        // Reload with relationships
        return $this->zoneRepository->findByIdOrFail($id);
    }

    /**
     * Delete a zone
     *
     * @param int $id
     * @return bool
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteZone(int $id): bool
    {
        $zone = $this->zoneRepository->findByIdOrFail($id);
        
        // Check if zone has associated countries or brokers
        $countriesCount = $zone->countries()->count();
        $brokersCount = $zone->brokers()->count();
        
        if ($countriesCount > 0 || $brokersCount > 0) {
            throw new \Exception(
                "Cannot delete zone. It has {$countriesCount} associated countries and {$brokersCount} associated brokers."
            );
        }
        
        return $this->zoneRepository->delete($zone);
    }

    /**
     * Check if zone_code is unique
     *
     * @param string $zoneCode
     * @param int|null $excludeId
     * @return bool
     */
    public function isZoneCodeUnique(string $zoneCode, ?int $excludeId = null): bool
    {
        return !$this->zoneRepository->zoneCodeExists($zoneCode, $excludeId);
    }

    /**
     * Get zone statistics
     *
     * @param int $id
     * @return array
     */
    public function getZoneStatistics(int $id): array
    {
        $zone = $this->zoneRepository->findByIdOrFail($id);
        
        return [
            'zone' => $zone,
            'countries_count' => $zone->countries()->count(),
            'brokers_count' => $zone->brokers()->count(),
        ];
    }
}

