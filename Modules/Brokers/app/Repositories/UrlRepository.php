<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\Url;
use App\Utilities\ModelHelper;
class UrlRepository
{
    public function create(array $data)
    {
        return Url::create($data);
    }

    public function update(Url $url, array $data)
    {
        $url->update($data);
        return $url;
    }

    public function find($id)
    {
        return Url::find($id);
    }

    public function findByAccountType($accountTypeId)
    {
        return Url::where('urlable_type', 'Modules\\Brokers\\Models\\AccountType')
            ->where('urlable_id', $accountTypeId)
            ->with('translations')
            ->get();
    }

    public function bulkCreate(array $data)
    {
        return Url::insert($data);
    }

    public function getUrlsByEntity($broker_id, $entity_type, $entity_id = null,$zone_code = null,$language_code = null)
    {
     //dd($broker_id,$entity_type,$entity_id,$zone_code,$language_code);
        $builder = Url::query()
            ->where('broker_id', $broker_id)
            ->where('urlable_type', $entity_type);

            
        if (is_numeric($entity_id)) {
            $builder->where('urlable_id', $entity_id);
        }

        if ($zone_code) {
            $builder->where(function($query) use ($zone_code) {
                $query->whereHas('zone', function($q) use ($zone_code) {
                    $q->where('zone_code', $zone_code);
                })->orWhere('is_invariant', '1');
            });
        } else {
            $builder->where('zone_id',null)->orWhere('zone_id',0);
        }

        if ($language_code && $language_code != 'en') {
            $builder = $builder->with(['translations' => function($query) use ($language_code) {
                $query->where('language_code', $language_code);
            }]);
        }

        return $builder->orderBy('id','desc')->get();
    }
} 