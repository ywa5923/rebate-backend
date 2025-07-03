<?php

namespace Modules\Translations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Translations\Database\Factories\TranslationFactory;
/**
 * @OA\Schema(
 *   schema="Translation",
 *   type="object",
 *   required={"language_code"},
 *   @OA\Property(property="id",type="integer", format="int64"),
 *   @OA\Property(property="translationable_type",type="string",nullable=false),
 *   @OA\Property(property="translationable_id",type="integer",nullable=false),
 *   @OA\Property(property="language_code",type="string",nullable=false),
 *   @OA\Property(property="property",type="string",nullable=true),
 *   @OA\Property(property="value",type="string",nullable=true),
 *   @OA\Property(property="translation_type",type="string",enum={"columns","property","properties"}),
 *   @OA\Property(property="metadata",type="string",nullable=true),
 *   @OA\Property(property="created_at",type="datetime",nullable=false),
 *   @OA\Property(property="updated_at",type="datetime",nullable=false)
 * )
 * Class Translation
 * @package Modules\Translations\Models
 */

class Translation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function translationable():MorphTo
    {
        return $this->morphTo();
    }
}
