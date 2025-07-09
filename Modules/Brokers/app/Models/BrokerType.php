<?php
namespace Modules\Brokers\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrokerType extends Model
{

    public function brokers():HasMany
    {
        return $this->hasMany(Broker::class);
    }
}
