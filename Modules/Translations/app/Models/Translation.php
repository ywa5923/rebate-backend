<?php

namespace Modules\Translations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Translations\Database\Factories\TranslationFactory;

class Translation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): TranslationFactory
    {
        return TranslationFactory::new();
    }

    public function translationable():MorphTo
    {
        return $this->morphTo();
    }
}
