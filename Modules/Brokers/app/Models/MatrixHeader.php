<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Translations\Models\Translation;
class MatrixHeader extends Model
{
   protected $fillable=['title','description','matrix_id','type'];
   protected $table='matrix_headers';

   public function matrix():BelongsTo
   {
    return $this->belongsTo(Matrix::class);
   }

   public function translations():MorphMany
   {
       return $this->morphMany(Translation::class,'translationable');
   }
   
}
