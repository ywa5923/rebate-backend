<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\ChallengeStep;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ChallengeStepRepository
{
    protected ChallengeStep $model;

    public function __construct(ChallengeStep $model)
    {
        $this->model = $model;
    }

      /**
     * Get challenge category by ID without relations
     */
    public function findById(int $id,?int $broker_id=null): ?ChallengeStep
    {
       
        $query = $this->model->newQuery();
        if(isset($broker_id)){
            $query->whereHas('challengeCategory',function($query) use ($broker_id){
                $query->where('broker_id', $broker_id);
            })->where('id', $id);
        }else{
            $query->where('id', $id);
        }
        return $query->first();
    }

    public function findDefaultStepBySlug(string $slug): ?ChallengeStep
    {
        return $this->model->newQuery()->whereHas('challengeCategory',function($query){
                $query->whereNull('broker_id');
            })->where('slug', $slug)->first();
    }
    public function findDefaultStepById(string $id): ?ChallengeStep
    {
        return $this->model->newQuery()->whereHas('challengeCategory',function($query){
                $query->whereNull('broker_id');
            })->where('id', $id)->first();
    }

    public function cloneStep(int $default_step_id_to_clone, int $order,int $broker_category_id): ChallengeStep
    {
        
        $defaultStep = $this->findDefaultStepById($default_step_id_to_clone);
        if(!$defaultStep){
            throw new \Exception('Step not found');
        }
        return $this->model->create([
            'slug' => $defaultStep->slug,
            'name'=>$defaultStep->name,
            'description'=>$defaultStep->description,
            'image'=>$defaultStep->image,
            'order' => $order,
            'challenge_category_id' => $broker_category_id,
        ]);
    }
}

