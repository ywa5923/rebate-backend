<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Brokers\Models\Challenge;



class ChallengeCategory extends Model
{
    

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'image',
        'slug',
        'order',
        'broker_id',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(ChallengeStep::class,'challenge_category_id');
    }

    public function amounts(): HasMany
    {
        return $this->hasMany(ChallengeAmount::class,'challenge_category_id');
    }
    public function challenges(): HasMany
    {
        return $this->hasMany(Challenge::class,'challenge_category_id');
    }

   
}
