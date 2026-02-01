<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Brokers\Models\DropdownCategory;
use Modules\Translations\Models\Translation;
use Illuminate\Database\Eloquent\Relations\MorphMany;
class DropdownOption extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'dropdown_category_id',
        'label',
        'value',
        'order',
    ];

    public function dropdownCategory()
    {
        return $this->belongsTo(DropdownCategory::class);
    }
    public function translations():MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

}
