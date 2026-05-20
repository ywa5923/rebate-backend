<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Brokers\Forms\CountryForm;
use Modules\Brokers\Http\Requests\CountryListRequest;
use Modules\Brokers\Http\Requests\StoreCountryRequest;
use Modules\Brokers\Http\Requests\UpdateCountryRequest;
use Modules\Brokers\Services\CountryService;
use Modules\Brokers\Tables\CountryTableConfig;
use Modules\Brokers\Transformers\CountryResource;

class CountryController extends Controller
{
    public function __construct(
        protected CountryService $countryService,
        private readonly CountryTableConfig $tableConfig,
        private readonly CountryForm $formConfig,
    ) {
    }

    /**
     * Get paginated list of countries with filters
     */
    public function index(CountryListRequest $request): JsonResponse
    {
        try {
            $filters = $request->getFilters();
            $orderBy = $request->getOrderBy();

            $orderDirection = $request->getOrderDirection();
            $perPage = $request->getPerPage();

            $countries = $this->countryService->getCountryList(
                $perPage,
                $orderBy,
                $orderDirection,
                $filters,
            );

            return response()->json([
                'success' => true,
                'data' => CountryResource::collection($countries->items()),
                'form_config' => $this->formConfig->getFormData(),
                'table_columns_config' => $this->tableConfig->columns(),
                'filters_config' => $this->tableConfig->filters(),
                'pagination' => [
                    'current_page' => $countries->currentPage(),
                    'last_page' => $countries->lastPage(),
                    'per_page' => $countries->perPage(),
                    'total' => $countries->total(),
                    'from' => $countries->firstItem(),
                    'to' => $countries->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to get country list',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get a single country by ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $country = $this->countryService->getCountryById($id);

            return response()->json([
                'success' => true,
                //'data' => new CountryResource($country),
                'data' => $country->only([
                    'id',
                    'name',
                    'country_code',
                    'zone_id',
                ]),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Country not found',
                ],
                404,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to get country',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function getFormConfig(): JsonResponse
    {
        try {
            return response()->json(
                [
                    'success' => true,
                    'data' => $this->formConfig->getFormData(),
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error getting form data',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Create a new country
     */
    public function store(StoreCountryRequest $request): JsonResponse
    {
        try {
            $country = $this->countryService->createCountry(
                $request->validated(),
            );

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Country created successfully',
                    'data' => new CountryResource($country),
                ],
                201,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to create country',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Update an existing country
     */
    public function update(UpdateCountryRequest $request, int $id): JsonResponse
    {
        try {
            $country = $this->countryService->updateCountry(
                $id,
                $request->validated(),
            );

            return response()->json([
                'success' => true,
                'message' => 'Country updated successfully',
                'data' => new CountryResource($country),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Country not found',
                ],
                404,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to update country',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Delete a country
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->countryService->deleteCountry($id);

            return response()->json([
                'success' => true,
                'message' => 'Country deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Country not found',
                ],
                404,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to delete country',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get country statistics (zone and brokers count)
     */
    public function statistics(int $id): JsonResponse
    {
        try {
            $stats = $this->countryService->getCountryStatistics($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'country' => new CountryResource($stats['country']),
                    'zone' => $stats['zone']
                        ? [
                            'id' => $stats['zone']->id,
                            'name' => $stats['zone']->name,
                            'zone_code' => $stats['zone']->zone_code,
                        ]
                        : null,
                    'brokers_count' => $stats['brokers_count'],
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Country not found',
                ],
                404,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to get country statistics',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Sanitize input for LIKE queries by escaping special characters
     */
    private function sanitizeLikeInput(string $input): string
    {
        // Escape special LIKE characters: %, _, \
        return str_replace(['\\', '%', '_'], ['\\\\', "\%", "\_"], $input);
    }
}
