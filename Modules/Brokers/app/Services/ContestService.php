<?php

namespace Modules\Brokers\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Brokers\DTOs\ContestFilters;
use Modules\Brokers\Models\Contest;
use Modules\Brokers\Repositories\ContestRepository;

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
    public function getContests(ContestFilters $filters, int $broker_id): Collection|LengthAwarePaginator
    {

        return $this->repository->getContests($filters, $broker_id);
    }

    /**
     * Get contest by ID
     */
    public function getContestById(int $id): ?Contest
    {
        return $this->repository->findById($id);
    }

    /**
     * Delete contest. Returns false when not found, wrong broker, or delete fails.
     */
    public function deleteContest(int $id, int $broker_id): ?Contest
    {
        $contest = $this->repository->findByIdWithoutRelations($id);

        if ($contest === null || $contest->broker_id !== $broker_id) {
            return null;
        }

        if (! $this->repository->delete($contest)) {
            return null;
        }

        return $contest;
    }
}
