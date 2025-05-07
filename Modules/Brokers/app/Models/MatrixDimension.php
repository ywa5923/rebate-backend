<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
class MatrixDimension extends Model
{
   protected $fillable=['type','order','matrix_id','matrix_header_id'];
   protected $table='matrix_dimensions';

   /**
    * Get the matrix name.
    */
   public function matrix():BelongsTo
   {
    return $this->belongsTo(Matrix::class);
   }

   /**
    * Get the matrix header.
    */
   public function matrixHeader():BelongsTo
   {
    return $this->belongsTo(MatrixHeader::class);
   }
   /**
    * Get the broker.
    */
   public function broker():BelongsTo
   {
    return $this->belongsTo(Broker::class);
   }

   /**
    * Get the matrix row cells.
    */
   public function matrixRowCells():HasMany
   {
    return $this->hasMany(MatrixValue::class,'matrix_row_id','id');
   }

   /**
    * Get the matrix values.
    */
   public function matrixValues():HasMany
   {
    
    if($this->type=='row')  
    {
        return $this->hasMany(MatrixValue::class,'matrix_row_id');
    }
    else
    {
        return $this->hasMany(MatrixValue::class,'matrix_column_id');
    }
   }

  
}
