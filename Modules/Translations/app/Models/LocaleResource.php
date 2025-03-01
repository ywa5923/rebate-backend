<?php

namespace Modules\Translations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Translations\Database\Factories\LocaleResourceFactory;

class LocaleResource extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): LocaleResourceFactory
    {
        //return LocaleResourceFactory::new();
    }
}
