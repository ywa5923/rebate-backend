<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DynamicOption extends Model
{
    use HasFactory;

    public function category():BelongsTo
    {
        return $this->belongsTo(DynamicOptionsCategory::class);
    }

    public function values():HasMany
    {
        return $this->hasMany(DynamicOptionsValue::class);
    }
}
