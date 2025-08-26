<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Brokers\Database\Factories\ChallengeStepFactory;

class ChallengeStep extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): ChallengeStepFactory
    {
        //return ChallengeStepFactory::new();
    }
}
