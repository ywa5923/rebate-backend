<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;



class ChallengeCategory extends Model
{
    

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function steps(): HasMany
    {
        return $this->hasMany(ChallengeStep::class,'challenge_category_id');
    }

    public function amounts(): HasMany
    {
        return $this->hasMany(ChallengeAmount::class,'challenge_category_id');
    }

   
}
