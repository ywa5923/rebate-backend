<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\Url;
use App\Utilities\ModelHelper;
class UrlRepository
{
    /**
     * Create url
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data)
    {
        return Url::create($data);
    }

    /**
     * Update url
     * @param Url $url
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(Url $url, array $data)
    {
        $url->update($data);
        return $url;
    }

    /**
     * Find url by id
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function find($id)
    {
        return Url::find($id);
    }

    /**
     * Find urls by account type
     * @param int $accountTypeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByAccountType($accountTypeId)
    {
        return Url::where('urlable_type', 'Modules\\Brokers\\Models\\AccountType')
            ->where('urlable_id', $accountTypeId)
            ->with('translations')
            ->get();
    }

    /**
     * Bulk create urls
     * @param array $data
     * @return bool
     */
    public function bulkCreate(array $data)
    {
        return Url::insert($data);
    }

    /**
     * Get urls by entity
     * @param int $broker_id
     * @param string $entity_type
     * @param int $entity_id
     * @param string $zone_code
     * @param string $language_code
     * @return \Illuminate\Database\Eloquent\Collection
     */
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
    /**
     * Delete urls by url type
     * @param string $urlType
     * @param int $brokerId
     * @return bool
     */
    public function deleteByUrlType($urlType, $brokerId)
    {
        return Url::where('url_type', $urlType)
            ->where('broker_id', $brokerId)
            ->delete();
    }

    public function deleteByUrlableType($urlableType, $brokerId)
    {
        return Url::where('urlable_type', $urlableType)
            ->where('broker_id', $brokerId)
            ->delete();
    }

   
    /**
     * Find url by urlable type and id
     * @param string $urlableType
     * @param int $urlableId
     * @param int $brokerId
     * @param int|null $zoneId
     * @return Url|null
     */
    public function findByUrlableTypeAndId($urlableType, $urlableId, $brokerId, $zoneId = null): ?Url
    {
        return Url::where('urlable_type', $urlableType)
            ->where('urlable_id', $urlableId)
            ->where('broker_id', $brokerId)
            ->where('zone_id', $zoneId)
            ->first();
    }
} 