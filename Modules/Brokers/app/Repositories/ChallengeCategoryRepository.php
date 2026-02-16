<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\ChallengeCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ChallengeCategoryRepository
{
    protected ChallengeCategory $model;

    public function __construct(ChallengeCategory $model)
    {
        $this->model = $model;
    }

    /**
     * Get paginated challenge categories with filters
     */
    public function getChallengeCategories(?int $broker_id=null): Collection
    {
        $query = $this->model->newQuery();
        if(isset($broker_id)){
            $query->where('broker_id', $broker_id);
        }else{
            $query->whereNull('broker_id');
        }
       
        // Always load relationships ordered by 'order' then 'id'
        $query->with([
            'steps' => function ($q) {
                $q->orderBy('order', 'asc')->orderBy('id', 'asc');
            },
            'amounts' => function ($q) {
                $q->orderBy('order', 'asc')->orderBy('id', 'asc');
            },
        ])->orderBy('order', 'asc')->orderBy('id', 'asc');

        
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
    public function findByIdWithoutRelations(int $id,?int $broker_id=null): ?ChallengeCategory
    {
        $query = $this->model->newQuery();
        if(isset($broker_id)){
            $query->where('id', $id)->where('broker_id', $broker_id);
        }else{
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
     * @param string $slug
     * @return ?ChallengeCategory
     */
    public function findDefaultCategoryBySlug(string $slug): ?ChallengeCategory
    {
        return $this->model->where('slug', $slug)->whereNull('broker_id')->first();
    }

    /**
     * Add a challenge category
     * @param string $slug
     * @param int $order
     * @param int $broker_id
     * @return ChallengeCategory
     */
    public function cloneCategory(int $default_category_id, int $order, int $broker_id): ChallengeCategory
    {
        $defaultCategory = $this->findByIdWithoutRelations($default_category_id);
        if(!$defaultCategory){
            throw new \Exception('Default category not found');
        }
        return $this->model->create([
            'slug' => $defaultCategory->slug,
            'name'=>$defaultCategory->name,
            'description'=>$defaultCategory->description,
            'image'=>$defaultCategory->image,
            'order' => $order,
            'broker_id' => $broker_id,
        ]);
    }
}
