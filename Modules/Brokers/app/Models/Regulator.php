<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Brokers\Database\Factories\RegulatorFactory;

class Regulator extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): RegulatorFactory
    {
        //return RegulatorFactory::new();
    }
}
