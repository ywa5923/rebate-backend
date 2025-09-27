<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\Url;
use App\Utilities\ModelHelper;
use Modules\Brokers\Models\Challenge;

class UrlRepository
{
    protected Url $model;

    public function __construct(Url $model)
    {
        $this->model = $model;
    }
    /**
     * Create url
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data)
    {
        return $this->model->create($data);
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
        return $this->model->find($id);
    }

    /**
     * Find urls by account type
     * @param int $accountTypeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByAccountType($accountTypeId)
    {
        return $this->model->where('urlable_type', 'Modules\\Brokers\\Models\\AccountType')
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
        return $this->model->insert($data);
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
        $builder = $this->model->newQuery()
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
        return $this->model->newQuery()->where('url_type', $urlType)
            ->where('broker_id', $brokerId)
            ->delete();
    }

    public function deleteByUrlableType($urlableType, $brokerId)
    {
        return $this->model->newQuery()->where('urlable_type', $urlableType)
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
    public function findByUrlableTypeAndId($urlableType, $urlableId, $brokerId,$isPlaceholder, $zoneId = null): ?Url
    {
       $qb= $this->model->newQuery()->where('urlable_type', $urlableType);
      
       if(isset($urlableId)){
        $qb->where('urlable_id', $urlableId);
       }else{
       
        $qb->whereNull('urlable_id');
       }
       if(isset($brokerId)){
        $qb->where('broker_id', $brokerId);
       }
       if(isset($zoneId)){
        $qb->where('zone_id', $zoneId);
       }
       if(isset($isPlaceholder)){
        $qb->where('is_placeholder', $isPlaceholder);
       }

       //dd($qb->getBindings(),$qb->toSql());
       return $qb->orderBy('id','desc')->first();
           
    }

    /** 
     * Save affiliate link
     * @param int|null $challengeId
     * @param string $affiliateLink
     * @param string $affiliateLinkName
     * @param int $brokerId
     * @param bool|null $isAdmin
     * @param bool|null $isPlaceholder
     * @param int|null $zoneId
     * @return void
     */
    public function saveAffiliateLink(
        ?int $challengeId=null, 
        string $affiliateLink, 
        string $affiliateLinkName, 
        int $brokerId, 
        ?bool $isAdmin = null,
        ?bool $isPlaceholder = false,
        ?int $zoneId = null,
       ): void
    {
        $field = ($isAdmin && !$isPlaceholder) ? 'public_url' : 'url';


        $this->create([
            'urlable_type' => Challenge::class,
            'urlable_id' => $challengeId ?? null,
            'url_type' => 'challenge-matrix',
            $field => $affiliateLink,
            'name' => $affiliateLinkName,
            'slug' => strtolower(str_replace(' ', '-', $affiliateLinkName)),
            'broker_id' => $brokerId,
            'is_placeholder' => $isPlaceholder,
            'zone_id' => $zoneId,
        ]);
    }
} 