<?php

namespace Modules\Translations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Translations\Database\Factories\LocaleFactory;

class Locales extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'locales';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ["country","code","description","flag_path"];

    protected static function newFactory(): LocaleFactory
    {
        //return LocaleFactory::new();
    }
}
