<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\CountryRepository;
use Modules\Brokers\Models\Country;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CountryService
{
    public function __construct(
        protected CountryRepository $countryRepository
    ) {
    }

    /**
     * Get all countries
     *
     * @return Collection
     */
    public function getAllCountries(): Collection
    {
        return $this->countryRepository->getAll();
    }

    /**
     * Get paginated countries with filters
     *
     * @param int $perPage
     * @param string $orderBy
     * @param string $orderDirection
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getCountryList(
        int $perPage = 15,
        string $orderBy = 'id',
        string $orderDirection = 'asc',
        array $filters = []
    ): LengthAwarePaginator {
        return $this->countryRepository->getPaginated($perPage, $orderBy, $orderDirection, $filters);
    }

    /**
     * Get country by ID
     *
     * @param int $id
     * @return Country
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getCountryById(int $id): Country
    {
        return $this->countryRepository->findByIdOrFail($id);
    }

    /**
     * Create a new country
     *
     * @param array $data
     * @return Country
     */
    public function createCountry(array $data): Country
    {
        return $this->countryRepository->create($data);
    }

    /**
     * Update a country
     *
     * @param int $id
     * @param array $data
     * @return Country
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateCountry(int $id, array $data): Country
    {
        $country = $this->countryRepository->findByIdOrFail($id);
        $this->countryRepository->update($country, $data);
        
        // Reload with relationships
        return $this->countryRepository->findByIdOrFail($id);
    }

    /**
     * Delete a country
     *
     * @param int $id
     * @return bool
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteCountry(int $id): bool
    {
        $country = $this->countryRepository->findByIdOrFail($id);
        
        // Check if country has associated brokers
        $brokersCount = $country->brokers()->count();
        
        if ($brokersCount > 0) {
            throw new \Exception(
                "Cannot delete country. It has {$brokersCount} associated brokers."
            );
        }
        
        return $this->countryRepository->delete($country);
    }

    /**
     * Check if country_code is unique
     *
     * @param string $countryCode
     * @param int|null $excludeId
     * @return bool
     */
    public function isCountryCodeUnique(string $countryCode, ?int $excludeId = null): bool
    {
        return !$this->countryRepository->countryCodeExists($countryCode, $excludeId);
    }

    /**
     * Get country statistics
     *
     * @param int $id
     * @return array
     */
    public function getCountryStatistics(int $id): array
    {
        $country = $this->countryRepository->findByIdOrFail($id);
        
        return [
            'country' => $country,
            'zone' => $country->zone,
            'brokers_count' => $country->brokers()->count(),
        ];
    }
}

