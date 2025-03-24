<?php

namespace Modules\Translations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Translations\Database\Factories\LocaleFactory;

class Locale extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ["country","code","description"];

    protected static function newFactory(): LocaleFactory
    {
        //return LocaleFactory::new();
    }
}
