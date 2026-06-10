<?php

namespace Modules\Brokers\Http\Controllers;

use App\DTO\ApiData;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Modules\Brokers\Enums\ChallengeTabEnum;
use Modules\Brokers\Repositories\MatrixHeaderRepository;
use Modules\Brokers\Services\ChallengeCategoryService;
use Modules\Brokers\Services\ChallengeService;
use Modules\Brokers\Services\DropdownListService;
use Modules\Brokers\Transformers\ChallengeCategoryResource;
use Modules\Brokers\Transformers\MatrixHeaderResource;

class ChallengeController extends Controller
{
    protected ChallengeCategoryService $challengeCategoryService;

    protected ChallengeService $challengeService;

    public function __construct(ChallengeCategoryService $challengeCategoryService, ChallengeService $challengeService)
    {
        $this->challengeCategoryService = $challengeCategoryService;
        $this->challengeService = $challengeService;
    }

    /**
     * Refactored show method using helper methods from ChallengeService
     */
    public function show(Request $request, int $broker_id): JsonResponse
    {
        $validatedData = $request->validate([
            'category_id' => 'required|integer|exists:challenge_categories,id',
            'step_id' => 'required|integer|exists:challenge_steps,id',
            'amount_id' => 'required|integer|exists:challenge_amounts,id',
            'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
        ]);

        $amountId = $validatedData['amount_id'];
        $zoneId = $validatedData['zone_id'] ?? null;
        $categoryId = $validatedData['category_id'];
        $stepId = $validatedData['step_id'];

        // the chalenge row for the current parameters
        $challenge = $this->challengeService->findChallengeByParams(
            false,
            $categoryId,
            $stepId,
            $amountId,
            $broker_id,
            $zoneId
        );
        $chId = $challenge?->id ?? null;

        $responseData = $this->challengeService->getChallengeData($chId, $broker_id, false, $zoneId);

        $isPublished = ($chId) ? $challenge->is_published : true;
        $responseData['is_published'] = $isPublished;

        $this->challengeService->addPlaceholderData($responseData, $broker_id, $categoryId, $stepId, $zoneId);

        return Response::json(ApiData::success(
            data: array_filter($responseData, fn ($v) => $v !== null),
        ));
    }

    /**
     * Show placeholders for a challenge
     */
    public function showPlaceholders(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'category_id' => 'required|integer|exists:challenge_categories,id',
            'step_id' => 'required|integer|exists:challenge_steps,id',
            'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
        ]);

        $amountId = null;  // for placeholders, the amount id is null

        $zoneId = $validatedData['zone_id'] ?? null;
        $categoryId = $validatedData['category_id'];
        $stepId = $validatedData['step_id'];

        // the chalenge row for the current parameters
        $challenge = $this->challengeService->findChallengeByParams(
            true,
            $categoryId,
            $stepId,
            $amountId,
            null,
            $zoneId
        );
        $chId = $challenge?->id ?? null;

        $responseData = $this->challengeService->getChallengeData($chId, null, true, $zoneId);

        return Response::json(ApiData::success(
            data: array_filter($responseData, fn ($v) => $v !== null),
        ));
    }

    /**
     * Get the challenge categories for a broker
     */
    public function getChallengeCategories(Request $request, int $broker_id): JsonResponse
    {
        $challengeCategories = $this->challengeCategoryService->getChallengeCategories($broker_id);

        return Response::json(ApiData::success(
            data: ChallengeCategoryResource::collection($challengeCategories),
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, int $broker_id): JsonResponse
    {
        $validatedData = $this->challengeService->validatePostRequestData($request);

        $zoneId = $validatedData['zone_id'] ?? null;

        $isPlaceholder = $validatedData['is_placeholder'];

        $isAdmin = $request->attributes->get('isAdmin', false);

        // process the request and update the challenge matrix and extra data using transaction

        ['challenge_id' => $challenge_id] = $this->challengeService->processRequest($validatedData, $broker_id, $isPlaceholder, $isAdmin, $zoneId);

        return Response::json(ApiData::success(
            message: 'Challenge matrix with id '.$challenge_id.' created successfully',
            data: $this->challengeService->getChallengeData($challenge_id, $broker_id, $isPlaceholder, $zoneId),
        ), 201);
    }

    /**
     * Store a newly created resource in storage for placeholders
     */
    public function storeMatrixPlaceholders(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'category_id' => 'required|integer|exists:challenge_categories,id',
            'step_id' => 'required|integer|exists:challenge_steps,id',
            // 'step_slug' => 'nullable|string',
            'amount_id' => 'nullable|integer|exists:challenge_amounts,id',
            'matrix' => 'required|array',
            'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
        ]);
        $zoneId = $validatedData['zone_id'] ?? null;

        // process the request and update the challenge matrix and extra data using transaction

        ['challenge_id' => $challenge_id] = $this->challengeService->processRequest($validatedData, null, true, true, $zoneId);

        return Response::json(ApiData::success(
            message: 'Challenge matrix with id '.$challenge_id.' created successfully',
            data: $this->challengeService->getChallengeData($challenge_id, null, true, $zoneId),
        ), 201);
    }

    public function getDefaultChallengeCategories(DropdownListService $dropdownListService): JsonResponse
    {
        $challengeCategories = $this->challengeCategoryService->getChallengeCategories(null);
        $amountCurrencies = $dropdownListService->getCurrencyListOptions();
        $responseData = [
            'default_challenge_categories' => ChallengeCategoryResource::collection($challengeCategories),
            'amount_currencies' => $amountCurrencies,
        ];

        return Response::json(ApiData::success(
            data: $responseData,
        ));
    }

    /**
     * Get the challenge matrix headers for a broker
     */
    public function getChallengeMatrixHeaders(Request $request, MatrixHeaderRepository $rep): JsonResponse
    {
        $validatedData = $request->validate([
            'col_group' => 'sometimes|string|max:145',
            'row_group' => 'sometimes|string|max:145',
            'language' => 'sometimes|string|max:15',
        ]);

        $columnHeaders = MatrixHeaderResource::collection($rep->getHeadearsByType(
            'column',
            null,
            null,
            $validatedData['col_group'] ?? null,
            $validatedData['language'],
            false
        ));

        $rowHeaders = $rep->getHeadearsByType(
            'row',
            null,
            null,
            $validatedData['row_group'] ?? null,
            $validatedData['language'],
            false
        );

        return Response::json(ApiData::success(
            data: [
                'columnHeaders' => $columnHeaders,
                'rowHeaders' => MatrixHeaderResource::collection($rowHeaders),
            ],
        ));
    }

    /**
     * Remove a challenge category, step or amount
     */
    public function removeChallengeTab(string $tab_type, int $broker_id, Request $request): JsonResponse
    {

        $validatedData = $request->validate([
            'category_id' => 'sometimes|integer|exists:challenge_categories,id',
            'step_id' => 'sometimes|integer|exists:challenge_steps,id',
            'amount_id' => 'sometimes|integer|exists:challenge_amounts,id',
        ]);
        $category_id = $validatedData['category_id'] ?? null;
        $step_id = $validatedData['step_id'] ?? null;
        $amount_id = $validatedData['amount_id'] ?? null;

        $tab_type = ChallengeTabEnum::tryFrom($tab_type);
        if ($tab_type === null) {
            return response()->json(['success' => false, 'message' => 'Invalid tab type'], 400);
        }

        if ($tab_type === ChallengeTabEnum::CATEGORY && $category_id) {
            $this->challengeCategoryService->deleteChallengeCategory($category_id, $broker_id);
        }
        if ($tab_type === ChallengeTabEnum::STEP && $step_id) {
            $this->challengeCategoryService->deleteChallengeCategoryStep($step_id, $broker_id);
        }
        if ($tab_type === ChallengeTabEnum::AMOUNT && $amount_id) {
            $this->challengeCategoryService->deleteChallengeCategoryAmount($amount_id, $broker_id);
        }

        //  $this->challengeCategoryService->removeChallengeCategory($broker_id, $category_id);

        return Response::json(ApiData::success(
            message: 'Challenge tab of type '.$tab_type->value.' removed successfully',
        ));
    }

    /**
     * Add a challenge tab
     */
    public function addChallengeTab(string $tab_type, int $broker_id, Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'default_tab_id_to_clone' => 'required|string|max:145',
            'tab_order' => 'sometimes|integer',
            'broker_challenge_category_id' => 'sometimes|string|max:145',
            'amount_currency' => 'sometimes|string|max:100',
        ]);
        $tab_type = ChallengeTabEnum::tryFrom($tab_type);
        if ($tab_type === null) {
            throw new ApiException('Invalid tab type', 400);
        }
        $default_tab_id_to_clone = $validatedData['default_tab_id_to_clone'];

        $tab_order = $validatedData['tab_order'] ?? 100;

        $broker_challenge_category_id = $validatedData['broker_challenge_category_id'] ?? null;

        $amountCurrency = $validatedData['amount_currency'] ?? null;
        if ($amountCurrency !== null && preg_match('/\(([^)]+)\)/', $amountCurrency, $matches)) {
            $amountCurrency = $matches[1];
        }

        if ($broker_challenge_category_id) {
            $broker_challenge_category = $this->challengeCategoryService->getChallengeCategoryByIdAndBroker($broker_challenge_category_id, $broker_id);
            if (! $broker_challenge_category) {
                throw new ApiException('Broker challenge category not found', 404);
            }
        }
        $insertedTab = $this->challengeCategoryService->addChallengeTabToBroker(
            $tab_type,
            $default_tab_id_to_clone,
            $tab_order,
            $broker_id,
            $broker_challenge_category_id,
            $amountCurrency
        );

        return Response::json(ApiData::success(
            message: 'Challenge tab added successfully',
            data: $insertedTab->toArray()
        ));
    }

    /**
     * Save the order of the challenge tabs
     */
    public function saveChallengeTabOrder(Request $request, int $broker_id, string $tab_type): JsonResponse
    {
        $validatedData = $request->validate([
            'tab_ids' => 'required|array',
        ]);
        $tabs = $validatedData['tab_ids'];
        $tab_type = ChallengeTabEnum::tryFrom($tab_type);
        if ($tab_type === null) {
            throw new ApiException('Invalid tab type', 400);
        }
        $this->challengeCategoryService->saveChallengeTabOrder($tabs, $broker_id, $tab_type);

        return Response::json(ApiData::success(
            message: 'Challenge tab order saved successfully',
        ));
    }

    /**
     * Toggle the publish state of a challenge
     */
    public function toggleChallengePublish(Request $request, int $broker_id): JsonResponse
    {
        $validatedData = $request->validate([
            'category_id' => 'required|integer|exists:challenge_categories,id',
            'step_id' => 'required|integer|exists:challenge_steps,id',
            'amount_id' => 'required|integer|exists:challenge_amounts,id',
            'is_published' => 'required|boolean',
        ]);
        $category_id = $validatedData['category_id'];
        $step_id = $validatedData['step_id'];
        $amount_id = $validatedData['amount_id'];
        $is_published = $validatedData['is_published'];
        $this->challengeService->toggleChallengePublish($is_published, $category_id, $step_id, $amount_id, $broker_id);

        return Response::json(ApiData::success(
            message: 'Challenge publish toggled successfully',
        ));
    }

    /**
     * Clone a challenge matrix
     */
    public function cloneChallenge(Request $request, int $broker_id): JsonResponse
    {
        $isAdmin = $request->attributes->get('isAdmin', false);
        $validatedData = $request->validate([
            'category_id' => 'required|integer|exists:challenge_categories,id',
            'step_id' => 'required|integer|exists:challenge_steps,id',
            'amount_id' => 'required|integer|exists:challenge_amounts,id',
        ]);
        $category_id = $validatedData['category_id'];
        $step_id = $validatedData['step_id'];
        $amount_id = $validatedData['amount_id'];
        $this->challengeService->cloneChallengeMatrix($category_id, $step_id, $amount_id, $broker_id, $isAdmin);

        return Response::json(ApiData::success(
            message: 'Challenge cloned successfully',
        ));
    }
}
