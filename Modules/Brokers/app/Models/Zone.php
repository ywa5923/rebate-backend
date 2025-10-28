<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Translations\Models\Country;
use Modules\Brokers\Models\Broker;

class Zone extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'zone_code',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function countries()
    {
        return $this->hasMany(Country::class);
    }
    
    public function brokers()
    {
        return $this->hasMany(Broker::class);
    }

    
}
