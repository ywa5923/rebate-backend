<?php

namespace Modules\Translations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Translations\Database\Factories\LocaleResourceFactory;
use Modules\Translations\Models\Translation;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LocaleResource extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];


    public function translations():MorphMany
    {
        return $this->morphMany(Translation::class,'translationable');
    }
}
