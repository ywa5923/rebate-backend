<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\Broker;


trait BrokerTrait
{

    function all()
    {
        return Broker::all();
    }

    function create(array $data)
    {
        return Broker::create($data);
    }
    function update(array $data, $id)
    {
        $user = Broker::findOrFail($id);
        $user->update($data);
        return $user;
    }

    function delete($id)
    {
        $user = Broker::findOrFail($id);
        $user->delete();
    }

    function find($id)
    {
        return Broker::findOrFail($id);
    }
}
