<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\UrlRepository;
use Modules\Brokers\Models\AccountType;
use Modules\Brokers\Models\Url;
use App\Utilities\ModelHelper;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UrlService
{
    protected $repository;

    public function __construct(UrlRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createMany($urlableTypeObject, string $entityType, array $urls)
    {
        foreach ($urls as &$urlData) {
            $modelClass = ModelHelper::getModelClassFromSlug($entityType);
            $urlData['urlable_type'] = $modelClass;
            $urlData['urlable_id'] = $urlableTypeObject ? $urlableTypeObject->id : null;
            $urlData['slug'] = !empty($urlData['slug']) ? $urlData['slug'] : Str::slug($urlData['name']);
            $urlData['created_at'] = now();
            $urlData['updated_at'] = now();
        }
        unset($urlData);

        $this->repository->bulkCreate($urls);
        return true;
    }

    public function updateMany(string $entityType, array $urls, $broker_id)
    {
        $updated = [];
        $modelClass = ModelHelper::getModelClassFromSlug($entityType);
        foreach ($urls as $urlData) {

            if (isset($urlData['id'])) {
                $url = $this->repository->find($urlData['id']);
                $urlData['urlable_type'] = $modelClass;

                if ($url && $url->urlable_type == $modelClass && $url->broker_id == $broker_id) {
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

    public function getUrlsByEntity($broker_id, $entity_type, $entity_id, $zone_code, $language_code)
    {


        $modelClass = ModelHelper::getModelClassFromSlug($entity_type);

        $urls = $this->repository->getUrlsByEntity($broker_id, $modelClass, $entity_id, $zone_code, $language_code);

        return $urls;

        // $builder = Url::with('translations')->where('broker_id', $broker_id)
        // ->where('urlable_type', $modelClass);
        // if(is_numeric($entity_id)){
        //     $builder->where('urlable_id', $entity_id);
        // }
        // $urls = $builder->get();
    }

    public function validateData($broker_id, $entity_type, $entity_id, $request)
    {
        $validated = $request->validate([
            'zone_code' => 'sometimes|string',
            'language_code' => 'sometimes|string',
        ]);

        if (!is_numeric($entity_id) && $entity_id != 'all') {
            throw new \Exception('Invalid entity ID:Entity ID must be a number or "all"');
        }
        if (!is_numeric($broker_id)) {
            throw new \Exception('Invalid broker ID:Broker ID must be a number');
        }
        $modelClass = ModelHelper::getModelClassFromSlug($entity_type);
        if (!class_exists($modelClass)) {
            throw new \Exception('Invalid entity type:Entity type must be a valid model class');
        }
    }
}
