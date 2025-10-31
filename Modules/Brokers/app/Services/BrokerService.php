<?php

namespace Modules\Brokers\Services;

use App\Repositories\RepositoryInterface;
use App\Utilities\BaseQueryParser;
use Modules\Brokers\Models\Broker;
// use Illuminate\Database\Eloquent\Collection;
// use Modules\Brokers\Models\Zone;
// use Modules\Brokers\Transformers\BrokerCollection;
// use Modules\Translations\Repositories\TranslationRepository;
// use Modules\Translations\Models\Translation;
// use Modules\Translations\Transformers\TranslationCollection;
// use Modules\Brokers\Repositories\BrokerRepository;

class BrokerService
{

    public function __construct(
        protected RepositoryInterface $repository)
    {
    }
    //the repository is BrokerRepository which is bounded to the BrokerService
    //automatically injected Providers/BrokersServiceProvider.php

   
    public function process(BaseQueryParser $queryParser)
    {

        /** @var  Modules\Brokers\Repositories\BrokerRepository $repo*/
        $repo=$this->repository;
        $columns=!empty($queryParser->getWhereInParam("columns"))?$queryParser->getWhereInParam("columns")[1]:null;
        $orderBy=!empty($queryParser->getOrderBy())?$queryParser->getOrderBy()[0]:null;
        $zoneCondition= $queryParser->getWhereParam("zone");
        $languageCondition = $queryParser->getWhereParam("language");
       
        if(empty($zoneCondition) || empty($languageCondition)){
            return response()->json(['error' => 'Zone and language parameters are required'], 422);
        }
       
       
        return $repo->getDynamicColumns(
            $languageCondition,
            //change to zone code in production
          $zoneCondition,
            $columns,
            $orderBy,
            $queryParser->getOrderDirection(),
            $queryParser->getAllFilters()
        );
       
    }

    /**
     * Get broker context for a given broker id
     * @param int $id
     * @return array
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getBrokerContext(int $id):array
    {
        $broker = Broker::with([
            'brokerType',
            'country.zone',
            'dynamicOptionsValues' => function($query) {
                $query->where('option_slug', '=', 'trading_name')->whereNull('zone_code');
            }
        ])->findOrFail($id);
        
        return [
            'success' => true,
            'data' => [
                'broker_id' => $broker->id,
                 'broker_type' => $broker->brokerType->name,
                 'broker_trading_name' => $broker->dynamicOptionsValues->first()->value,
                 'country_id' => $broker->country->id,
                 'zone_id' => $broker->country->zone->id,
                'country_code' => $broker->country->country_code ?? null,
                'zone_code' => $broker->country->zone->zone_code ?? null,
            ],
        ];
    }



    public function getBrokerList($perPage = 15, $orderBy = 'id', $orderDirection = 'asc', $filters = [])
    {
        $query = Broker::query();
        
        // Apply individual filters
        if (!empty($filters['broker_type'])) {
            $query->whereHas('brokerType', function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['broker_type']}%");
            });
        }
        
        if (!empty($filters['country'])) {
            $query->whereHas('country', function($q) use ($filters) {
                $q->where('country_code', 'like', "%{$filters['country']}%")
                  ->orWhere('name', 'like', "%{$filters['country']}%");
            });
        }
        
        if (!empty($filters['zone'])) {
            $query->whereHas('zone', function($q) use ($filters) {
                $q->where('zone_code', 'like', "%{$filters['zone']}%");
            });
        }
        
        if (!empty($filters['trading_name'])) {
            $query->whereHas('dynamicOptionsValues', function($q) use ($filters) {
                $q->where('option_slug', 'trading_name')
                  ->whereNull('zone_code')
                  ->where('value', 'like', "%{$filters['trading_name']}%");
            });
        }
        
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        
        // Handle relationship ordering with joins
        if ($orderBy === 'broker_type') {
            $query->leftJoin('broker_types', 'brokers.broker_type_id', '=', 'broker_types.id')
                  ->orderBy('broker_types.name', $orderDirection)
                  ->select('brokers.*');
        } elseif ($orderBy === 'country') {
            $query->leftJoin('countries', 'brokers.country_id', '=', 'countries.id')
                  ->orderBy('countries.name', $orderDirection)
                  ->select('brokers.*');
        } elseif ($orderBy === 'zone') {
            $query->leftJoin('zones', 'brokers.zone_id', '=', 'zones.id')
                  ->orderBy('zones.zone_code', $orderDirection)
                  ->select('brokers.*');
        } elseif ($orderBy === 'trading_name') {
            $query->leftJoin('option_values', function($join) {
                  $join->on('brokers.id', '=', 'option_values.optionable_id')
                       ->where('option_values.optionable_type', '=', 'Modules\\Brokers\\Models\\Broker')
                       ->where('option_values.option_slug', '=', 'trading_name')
                       ->whereNull('option_values.zone_code');
              })
              ->orderBy('option_values.value', $orderDirection)
              ->select('brokers.*');
        } else {
            // Direct column ordering
            $query->orderBy($orderBy, $orderDirection);
        }

        $query->with([
            'brokerType',
            'country',
            'zone',
            'dynamicOptionsValues' => function($query) {
                $query->whereIn('option_slug', ['trading_name', 'logo', 'home_url'])->whereNull('zone_code');
            }
        ]);

        return $query->paginate($perPage);
    }
    public function toggleActiveStatus($id)
    {
        $broker = Broker::findOrFail($id);
        $broker->is_active = !$broker->is_active;
        $broker->save();
        return $broker;
    }
}
