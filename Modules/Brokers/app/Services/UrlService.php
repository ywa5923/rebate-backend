<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\UrlRepository;
use Modules\Brokers\Models\AccountType;
use Modules\Brokers\Models\Url;
use App\Utilities\ModelHelper;
use Illuminate\Http\Request;

class UrlService
{
    protected $repository;

    public function __construct(UrlRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createMany(AccountType $accountType, array $urls)
    {
        foreach ($urls as &$urlData) {
            $urlData['urlable_type'] = AccountType::class;
            $urlData['urlable_id'] = $accountType->id;
            $urlData['created_at'] = now();
            $urlData['updated_at'] = now();
        }
        unset($urlData);

        $this->repository->bulkCreate($urls);

        // Optionally, fetch the created URLs (e.g., by account type and created_at)
        // return Url::where('urlable_type', AccountType::class)
        //     ->where('urlable_id', $accountType->id)
        //     ->where('created_at', '>=', now()->subSeconds(5))
        //     ->get();

        return true;
    }

    public function updateMany(AccountType $accountType, array $urls)
    {
        $updated = [];
        foreach ($urls as $urlData) {
            if (isset($urlData['id'])) {
                $url = $this->repository->find($urlData['id']);
                if ($url && $url->urlable_id == $accountType->id && $url->urlable_type == AccountType::class) {
                    $updated[] = $this->repository->update($url, $urlData);
                }
            }
        }
        return $updated;
    }

    public function getGroupedByType(AccountType $accountType)
    {
        $urls = $this->repository->findByAccountType($accountType->id);
        return $urls->groupBy('url_type')->map(fn($items) => $items->values());
    }

    public function getUrlsByEntity($broker_id, $entity_type, $entity_id,$zone_code,$language_code)
    {
        
    
        $modelClass = ModelHelper::getModelClassFromSlug($entity_type);

        $urls=$this->repository->getUrlsByEntity($broker_id, $modelClass, $entity_id,$zone_code,$language_code);
       
        return $urls;

        // $builder = Url::with('translations')->where('broker_id', $broker_id)
        // ->where('urlable_type', $modelClass);
        // if(is_numeric($entity_id)){
        //     $builder->where('urlable_id', $entity_id);
        // }
        // $urls = $builder->get();
    }

    public function validateData($broker_id,$entity_type,$entity_id,$request){
        $validated = $request->validate([
            'zone_code' => 'sometimes|string',
            'language_code' => 'sometimes|string',
        ]);

        if(!is_numeric($entity_id) && $entity_id != 'all'){
            throw new \Exception('Invalid entity ID:Entity ID must be a number or "all"');
        }
        if(!is_numeric($broker_id)){
            throw new \Exception('Invalid broker ID:Broker ID must be a number');
        }
        $modelClass = ModelHelper::getModelClassFromSlug($entity_type);
        if(!class_exists($modelClass)){
            throw new \Exception('Invalid entity type:Entity type must be a valid model class');
        }
       
    }

    
} 