<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Translations\Models\Translation;
class MatrixValue extends Model
{
   protected $fillable=['value','matrix_id','previous_value','public_value','matrix_row_id','matrix_column_id','broker_id'];
   protected $table='matrix_values';

   public function matrix():BelongsTo
   {
    return $this->belongsTo(Matrix::class);
   }

   public function matrixRow():BelongsTo
   {
       return $this->belongsTo(MatrixDimension::class,'matrix_row_id');
   }

   public function matrixColumn():BelongsTo 
   {
    return $this->belongsTo(MatrixDimension::class,'matrix_column_id');
   }

   public function broker():BelongsTo
   {
    return $this->belongsTo(Broker::class,'broker_id');
   }

   public function translations():MorphMany
   {
    return $this->morphMany(Translation::class,'translationable');
   }
}