<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Brokers\Models\ChallengeCategory;
use Modules\Brokers\Repositories\ChallengeAmountRepository;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ApiException;
class ChallengeCategoryRepository
{
    

    public function __construct(
        protected ChallengeCategory $model,
        protected ChallengeAmountRepository $challengeAmountRepository,
        protected ChallengeStepRepository $challengeStepRepository
    ) {}

    /**
     * Get paginated challenge categories with filters
     */
    public function getChallengeCategories(?int $broker_id = null): Collection
    {
        $query = $this->model->newQuery();
        if (isset($broker_id)) {
            $query->where('broker_id', $broker_id);
        } else {
            $query->whereNull('broker_id');
        }

        // Always load relationships ordered by 'order' then 'id'
        $query->with([
            'steps' => function ($q) {
                $q->orderBy('order', 'asc')->orderBy('name', 'asc');
            },
            'amounts' => function ($q) {
                $q->orderBy('order', 'asc')->orderBy('amount', 'asc');
            },
        ])->orderBy('order', 'asc')->orderBy('name', 'asc');

        return $query->get();
    }

    /**
     * Get challenge category by ID with relations
     */
    public function findById(int $id): ?ChallengeCategory
    {
        return $this->model->with([
            'steps' => function ($q) {
                $q->orderBy('order', 'asc')->orderBy('id', 'asc');
            },
            'amounts' => function ($q) {
                $q->orderBy('order', 'asc')->orderBy('id', 'asc');
            },
        ])->find($id);
    }

    /**
     * Get challenge category by ID without relations
     */
    public function findByIdWithoutRelations(int $id, ?int $broker_id = null): ?ChallengeCategory
    {
        $query = $this->model->newQuery();
        if (isset($broker_id)) {
            $query->where('id', $id)->where('broker_id', $broker_id);
        } else {
            $query->where('id', $id);
        }

        return $query->first();
    }

    /**
     * Create new challenge category
     */
    public function create(array $data): ChallengeCategory
    {
        return $this->model->create($data);
    }

    /**
     * Update challenge category
     */
    public function update(ChallengeCategory $challengeCategory, array $data): bool
    {
        return $challengeCategory->update($data);
    }

    /**
     * Delete challenge category
     */
    public function delete(ChallengeCategory $challengeCategory): bool
    {
        return $challengeCategory->delete();
    }

    /**
     * Find default challenge category by slug
     *
     * @return ?ChallengeCategory
     */
    public function findDefaultCategoryBySlug(string $slug): ?ChallengeCategory
    {
        return $this->model->where('slug', $slug)->whereNull('broker_id')->first();
    }

    /**
     * Add a challenge category
     *
     */
    public function cloneCategory(int $default_category_id, int $order, int $broker_id): ChallengeCategory
    {
        $defaultCategory = $this->findByIdWithoutRelations($default_category_id);
        if (! $defaultCategory) {
            throw new \Exception('Default category not found');
        }
       

       return DB::transaction(function () use ($defaultCategory, $order, $broker_id) {
       $challengeCategory = $this->model->create([
            'slug' => $defaultCategory->slug,
            'name' => $defaultCategory->name,
            'description' => $defaultCategory->description,
            'image' => $defaultCategory->image,
            'order' => $order,
            'broker_id' => $broker_id,
        ]);
        if(!$challengeCategory){
            throw new ApiException('Failed to create challenge category', 500);
        }
         $defaultAmounts = $defaultCategory->amounts()->get();
        foreach ($defaultAmounts as $defaultAmount) {
            $this->challengeAmountRepository->cloneAmount($defaultAmount->id, $defaultAmount->order??0, $challengeCategory->id, $defaultAmount->currency);
        }
        $defaultSteps = $defaultCategory->steps()->get();
        foreach ($defaultSteps as $defaultStep) {
            $this->challengeStepRepository->cloneStep($defaultStep->id, $defaultStep->order??0, $challengeCategory->id);
        }
        return $challengeCategory;
    });
    }
}
