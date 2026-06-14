<?php

namespace Modules\Brokers\Http\Controllers;

use App\DTO\ApiData;
use App\DTO\PaginationMeta;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Modules\Brokers\DTOs\OptionValueFilters;
use Modules\Brokers\Http\Requests\IndexOptionValueRequest;
use Modules\Brokers\Http\Requests\StoreOptionValueRequest;
use Modules\Brokers\Services\OptionValueService;
use Modules\Brokers\Transformers\OptionValueResource;

class OptionValueController extends Controller
{
    protected OptionValueService $optionValueService;

    public function __construct(OptionValueService $optionValueService)
    {
        $this->optionValueService = $optionValueService;
    }

    /**
     * Get all option values for a specific broker
     *
     * @param IndexOptionValueRequest $request
     * @param int $broker_id
     * @return JsonResponse
     */
    public function index(IndexOptionValueRequest $request, int $broker_id): JsonResponse
    {
        $filters = OptionValueFilters::from($request->validated());
        $optionValues = $this->optionValueService->getOptionValues(
            $filters,
            $broker_id,
        );

        return Response::json(ApiData::success(
            data: OptionValueResource::collection($optionValues)->resolve(),
            pagination: PaginationMeta::fromPaginatorOrNull($optionValues),
        ));
    }

    /**
     * Create multiple option values for a specific broker
     *
     * @param StoreOptionValueRequest $request
     * @param int $brokerId
     * @return JsonResponse
     */
    public function storeMultiple(StoreOptionValueRequest $request, int $brokerId): JsonResponse
    {
        // $isAdmin=app('isAdmin');

        $isAdmin = $request->attributes->get('isAdmin', false);
        // Example request
        // {
        //     "option_values": [
        //       {
        //         "id": null,
        //         "option_slug": "account_name",
        //         "value": "swsws",
        //         "metadata": null
        //       }
        //     ],
        //     "entity_id": 0,
        //     "entity_type": "AccountType"
        //   }

        $validatedData = $request->validated();
        $entityType = $validatedData['entity_type'];
        $entityId = $validatedData['entity_id'];
        $optionValues = $validatedData['option_values'];

        DB::transaction(function () use (
            $request,
            $brokerId,
            $entityType,
            $isAdmin,
            $optionValues,
        ) {
            $modelClass = is_null($entityType)
                ? $this->optionValueService->getModelClassFromSlug('Broker')
                : $this->optionValueService->getModelClassFromSlug(
                    $entityType,
                );
            $entityId =
                is_null($entityType) || $entityType == 'Broker' || $entityType == 'broker'
                    ? $brokerId
                    : $this->optionValueService->saveModelInstance(
                        $modelClass,
                        $brokerId,
                    );

            $this->optionValueService->createMultipleOptionValues(
                $brokerId,
                $isAdmin,
                $modelClass,
                $entityId,
                $optionValues,
            );

            if (str_contains($modelClass, 'AccountType')) {
                //for account type, save the account type name in the matrix headers table
                //this name appears in rebate matrix as a column name
                $accountNameOption = array_filter($optionValues, function (
                    $optionValue,
                ) {
                    return $optionValue['option_slug'] ==
                        'account_type_name';
                });

                $accountName = $accountNameOption[0]['value'];

                $this->optionValueService->createMatrixHeader([
                    'title' => $accountName,
                    'slug' => strtolower(
                        str_replace(' ', '-', $accountName),
                    ),
                    'broker_id' => $brokerId,
                    'type' => 'column',
                    'is_invariant' => true,
                    'parent_id' => null,
                    'form_type_id' => 4,
                    'matrix_id' => 1,
                ]);
            }
        });

        return Response::json(ApiData::success(
            data: $optionValues,
            message: 'Option values created successfully',
        ));
    }

    /**
     * Update multiple option values for a specific broker
     *
     * @param StoreOptionValueRequest $request
     * @param int $brokerId
     * @return JsonResponse
     */
    public function updateMultiple(
        StoreOptionValueRequest $request,
        int $brokerId,
    ): JsonResponse {
        // $isAdmin = app("isAdmin");

        $isAdmin = $request->attributes->get('isAdmin', false);
        $validatedData = $request->validated();
        $entityType = $validatedData['entity_type'];
        $entityId = $validatedData['entity_id'];
        $optionValues = $validatedData['option_values'];

        $this->optionValueService->validateEntityTypeAndId(
            $entityType,
            $entityId,
            $brokerId,
            $isAdmin,
        );

        $this->optionValueService->updateMultipleOptionValues(
            $isAdmin,
            $brokerId,
            $entityId,
            $entityType,
            $optionValues,
        );

        return Response::json(ApiData::success(
            data: $optionValues,
            message: 'Option values updated successfully',
        ));
    }
}
