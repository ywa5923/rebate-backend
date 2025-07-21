<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\UrlRepository;
use Modules\Brokers\Models\AccountType;

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
} 