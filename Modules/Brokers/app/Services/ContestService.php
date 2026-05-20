<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\DTOs\ContestFilters;
use Modules\Brokers\Models\Contest;
use Modules\Brokers\Repositories\ContestRepository;
use Modules\Brokers\Transformers\ContestResource;

class ContestService
{
    protected ContestRepository $repository;

    public function __construct(ContestRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get paginated contests with filters
     * @param ContestFilters $filters
     * @param int $broker_id
     * @return array
     * @throws \Exception
     */
    public function getContests(ContestFilters $filters, int $broker_id): array
    {

        $contests = $this->repository->getContests($filters, $broker_id);

        $response = [
            'success' => true,
            'data' => ContestResource::collection($contests),
        ];

        if ($filters->base->perPage || $filters->base->page) {
            $response['pagination'] = [
                'current_page' => $contests->currentPage(),
                'last_page' => $contests->lastPage(),
                'per_page' => $contests->perPage(),
                'total' => $contests->total(),
            ];
        }

        return $response;
    }

    /**
     * Get contest by ID
     * @param int $id
     * @return Contest|null
     */
    public function getContestById(int $id): ?Contest
    {
        return $this->repository->findById($id);
    }

    /**
     * Delete contest. Returns false when not found, wrong broker, or delete fails.
     * @param int $id
     * @param int $broker_id
     * @return Contest|null
     */
    public function deleteContest(int $id, int $broker_id): ?Contest
    {
        $contest = $this->repository->findByIdWithoutRelations($id);

        if ($contest === null || $contest->broker_id !== $broker_id) {
            return null;
        }

        if (!$this->repository->delete($contest)) {
            return null;
        }

        return $contest;
    }
}
