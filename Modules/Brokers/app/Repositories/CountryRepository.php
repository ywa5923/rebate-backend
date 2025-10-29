<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\Country;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CountryRepository
{
    /**
     * Get all countries
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Country::with(['zone', 'brokers'])->get();
    }

    /**
     * Get paginated countries with optional filters
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
        $query = Country::query();

        // Apply filters
        if (!empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (!empty($filters['country_code'])) {
            $query->where('country_code', 'like', "%{$filters['country_code']}%");
        }

        if (!empty($filters['zone_code'])) {
            $query->whereHas('zone', function ($q) use ($filters) {
                $q->where('zone_code', 'like', "%{$filters['zone_code']}%");
            });
        }

        // Handle relationship ordering with joins
        if ($orderBy === 'zone_code') {
            $query->select('countries.*')
                  ->join('zones', 'countries.zone_id', '=', 'zones.id')
                  ->orderBy('zones.name', $orderDirection);
        } else {
            $query->orderBy($orderBy, $orderDirection);
        }

        // Load relationships
        $query->with(['zone', 'brokers']);

        return $query->paginate($perPage);
    }

    /**
     * Find country by ID
     *
     * @param int $id
     * @return Country|null
     */
    public function findById(int $id): ?Country
    {
        return Country::with(['zone', 'brokers'])->find($id);
    }

    /**
     * Find country by ID or fail
     *
     * @param int $id
     * @return Country
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByIdOrFail(int $id): Country
    {
        return Country::with(['zone', 'brokers'])->findOrFail($id);
    }

    /**
     * Find country by country_code
     *
     * @param string $countryCode
     * @return Country|null
     */
    public function findByCountryCode(string $countryCode): ?Country
    {
        return Country::where('country_code', $countryCode)->first();
    }

    /**
     * Create a new country
     *
     * @param array $data
     * @return Country
     */
    public function create(array $data): Country
    {
        return Country::create($data);
    }

    /**
     * Update a country
     *
     * @param Country $country
     * @param array $data
     * @return bool
     */
    public function update(Country $country, array $data): bool
    {
        return $country->update($data);
    }

    /**
     * Delete a country
     *
     * @param Country $country
     * @return bool|null
     * @throws \Exception
     */
    public function delete(Country $country): ?bool
    {
        return $country->delete();
    }

    /**
     * Check if country_code exists
     *
     * @param string $countryCode
     * @param int|null $excludeId
     * @return bool
     */
    public function countryCodeExists(string $countryCode, ?int $excludeId = null): bool
    {
        $query = Country::where('country_code', $countryCode);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Get countries count
     *
     * @return int
     */
    public function count(): int
    {
        return Country::count();
    }
}

