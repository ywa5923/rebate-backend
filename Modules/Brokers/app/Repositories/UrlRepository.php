<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\Url;

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
} 