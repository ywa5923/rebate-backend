<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DynamicOptionsValue extends Model
{
    use HasFactory;

    public function option():BelongsTo
    {
          return $this->belongsTo(DynamicOption::class,"dynamic_option_id");
    }
}
