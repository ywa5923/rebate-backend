<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\Broker;
use Illuminate\Database\Eloquent\Collection;


trait BrokerTrait
{

    function all(): Collection
    {
        return Broker::all();
    }

    function create(array $data): Broker
    {
        return Broker::create($data);
    }

    function update(array $data, $id): Broker
    {
        $user = Broker::findOrFail($id);
        $user->update($data);
        return $user->fresh();
    }

    function delete($id): bool
    {
        $user = Broker::findOrFail($id);
        return $user->delete();
    }

    function find($id): Broker
    {
        return Broker::findOrFail($id);
    }
}
