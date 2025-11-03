<?php
namespace Modules\Brokers\Repositories;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

use Modules\Brokers\Models\BrokerOption;
use Modules\Brokers\Models\OptionCategory;
use Illuminate\Support\Collection;
class BrokerOptionRepository implements RepositoryInterface
{
   use RepositoryTrait;

    protected BrokerOption $model;
    public function __construct(BrokerOption $model)
    {
        $this->model = $model;
    }

    public function getDropdownOptionsDefaultLanguage($lang="en"):Collection
    {
        return BrokerOption::without(["translations"])
        ->where(function ($query){
            $query->where("for_brokers",1);
        })
        ->where("load_in_dropdown",1)->
        orWhere("default_loading",1)
        ->orderBy("default_loading","asc")
        ->get();
    }
    public function getDropdownOptions($langCondition):Collection
    {
       //$langCondition=["language_code","=","ro"];
    // return ($langCondition[2]=="en")? $this->getDropdownOptionsDefaultLanguage():$this->getDropdownOptionsByLanguageCode($langCondition);
       
      return $this->getDropdownOptionsByLanguageCode($langCondition);
    }

    public function getDropdownOptionsByLanguageCode($langCondition):Collection
    {
        return BrokerOption::with(["translations"=>function (Builder $query) use ($langCondition){
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            $query->where(...$langCondition);
       }])
        ->where(function ($query){
        $query->where("for_brokers",1);
        })
        ->where(function ($query){
            $query->where("load_in_dropdown",1)
            ->orWhere("default_loading",1);
        })->orderBy("default_loading","asc")
        ->get();  
    }

    public function getBrokerOptions(array $langCondition,int $brokerId, ?string $brokerType = null): Collection
    {
        return OptionCategory::with([
           
            'options' => function (Builder $query) use ($brokerType) {
                  /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                match ($brokerType) {
                    'props'   => $query->where('for_props', 1),
                    'brokers' => $query->where('for_brokers', 1),
                    'crypto'  => $query->where('for_crypto', 1),
                    default   => null, // No additional filtering
                };
    
                $query->orderBy('category_position', 'asc');
            },
            'options.translations' => function (Builder $query) use ($langCondition) {
                /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                $query->where(...$langCondition);
            },
            'options.values' => function (Builder $query) use ($brokerId) {
                /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                $query->where("broker_id",$brokerId);
            },
            'translations'         => function (Builder $query) use ($langCondition) {
                /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                $query->where(...$langCondition);
            },
        ])
        ->orderBy('position', 'asc')
        ->get();
    }

    public function getAllBrokerOptions(array $filters=[],string $orderBy='id',string $orderDirection='asc',int $perPage=15):LengthAwarePaginator
    {

        $query = $this->model->newQuery();

        if(!empty($filters['category_name']))
        {
            $query->whereHas('category', function ($query) use ($filters) {
                $query->where('name', 'like', "%{$filters['category_name']}%");
            });
        }
        
        if(!empty($filters['dropdown_category_name']))
        {
            $query->whereHas('dropdownCategory', function ($query) use ($filters) {
                $query->where('name', 'like', "%{$filters['dropdown_category_name']}%");
            });
        }

        if(!empty($filters['name']))
        {
            $query->where('name', 'like', "%{$filters['name']}%");
        }
        
        if(!empty($filters['applicable_for']))
        {
            $query->where('applicable_for', 'like', "%{$filters['applicable_for']}%");
        }

        if(!empty($filters['data_type']))
        {
            $query->where('data_type', 'like', "%{$filters['data_type']}%");
        }
        
        if(!empty($filters['form_type']))
        {
            $query->where('form_type', 'like', "%{$filters['form_type']}%");
        }
        
        if(isset($filters['for_brokers']))
        {
            $query->where('for_brokers', intval($filters['for_brokers']));
        }

        if(isset($filters['for_crypto']))
        {
            $query->where('for_crypto', intval($filters['for_crypto']));
        }
        
        if(isset($filters['for_props']))
        {
            $query->where('for_props', intval($filters['for_props']));
        }

        if(isset($filters['required']))
        {
            $query->where('required', intval($filters['required']));
        }
        
        if($orderBy=='category_name'){
            $query->leftJoin('option_categories', 'broker_options.option_category_id', '=', 'option_categories.id')
                  ->orderBy('option_categories.name', $orderDirection);
        }elseif($orderBy=='dropdown_category_name'){
            $query->leftJoin('dropdown_categories', 'broker_options.dropdown_category_id', '=', 'dropdown_categories.id')
                  ->orderBy('dropdown_categories.name', $orderDirection);
        }else{
            $query->orderBy($orderBy, $orderDirection);
        }
        
      
        return $query->with('category','dropdownCategory')->paginate($perPage);
    }

    /**
     * Create new broker option
     */
    public function create(array $data): BrokerOption
    {
        return $this->model->create($data);
    }

    /**
     * Update broker option
     */
    public function update(array $data, $id): ?BrokerOption
    {
        $brokerOption = $this->model->find($id);
        if($brokerOption)
        {
            $brokerOption->update($data);
            return $brokerOption->fresh();
        }
        return null;
    }

    /**
     * Delete broker option
     */
    public function delete($id): bool
    {
        $brokerOption = $this->model->find($id);
        if (!$brokerOption) {
            return false;
        }
        
        return $brokerOption->delete();
    }

    

    
}