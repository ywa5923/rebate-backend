<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\ContestRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\Models\Contest;

class ContestService
{
    protected ContestRepository $repository;

    public function __construct(ContestRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get paginated contests with filters
     */
    public function getContests(Request $request,int $broker_id): array
    {
        try {
            $contests = $this->repository->getContests($request,$broker_id);

            $response = [
                'success' => true,
                'data' => $contests,
            ];

            if ($request->has('per_page')) {
                $response['pagination'] = [
                    'current_page' => $contests->currentPage(),
                    'last_page' => $contests->lastPage(),
                    'per_page' => $contests->perPage(),
                    'total' => $contests->total(),
                ];
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('ContestService getContests error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get contest by ID
     */
    public function getContestById(int $id): ?Contest
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new contest
     */
    public function createContest(array $data): Contest
    {
        return DB::transaction(function () use ($data) {
            try {
                // Validate data
                $validatedData = $this->validateContestData($data);
                
                // Create contest
                $contest = $this->repository->create($validatedData);

                return $contest->load(['broker']);

            } catch (\Exception $e) {
                Log::error('ContestService createContest error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Update contest
     */
    public function updateContest(int $id, array $data): Contest
    {
        return DB::transaction(function () use ($id, $data) {
            try {
                $contest = $this->repository->findByIdWithoutRelations($id);
                
                if (!$contest) {
                    throw new \Exception('Contest not found');
                }

                // Validate data
                $validatedData = $this->validateContestData($data, true);
                
                // Update contest
                $this->repository->update($contest, $validatedData);

                return $contest->load(['broker']);

            } catch (\Exception $e) {
                Log::error('ContestService updateContest error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Delete contest
     */
    public function deleteContest(int $id, $broker_id): bool
    {
        try {
            $contest = $this->repository->findByIdWithoutRelations($id);
            
            if (!$contest) {
                throw new \Exception('Contest not found');
            }

            if ($contest->broker_id != $broker_id) {
                throw new \Exception('You are not authorized to delete this contest');
            }

            DB::beginTransaction();
            $this->repository->delete($contest);
            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ContestService deleteContest error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate contest data
     */
    public function validateContestData(array $data, bool $isUpdate = false): array
    {
        $rules = [
            'broker_id' => $isUpdate ? 'sometimes|required|exists:brokers,id' : 'required|exists:brokers,id',
            'title' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean',
            'contest_type' => 'nullable|string|max:100',
            'prize_pool' => 'nullable|string|max:255',
            'entry_requirements' => 'nullable|string',
            'rules' => 'nullable|string',
            'max_participants' => 'nullable|integer|min:1',
            'current_participants' => 'nullable|integer|min:0',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        return $validator->validated();
    }
} 