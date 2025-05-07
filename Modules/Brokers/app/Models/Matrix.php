<?php

namespace Modules\Brokers\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Matrix extends Model
{
   protected $fillable=['name','description'];
   protected $table='matrices';

   public function headers():HasMany
   {
    return $this->hasMany(MatrixHeader::class);
   }

   public function dimensions():HasMany
   {
    return $this->hasMany(MatrixDimension::class);
   }

   public function values():HasMany
   {
    return $this->hasMany(MatrixValue::class);
   }

   public function columns($broker_id,$zone_id=null):HasMany
   {
    return $this->dimensions()->where('type','column')
    ->where('broker_id',$broker_id)->
    where(function($query) use ($zone_id){
        
            $query->where('zone_id',$zone_id)
            ->orWhere('is_invariant',true);
        
    });
   }

   public function rows($broker_id,$zone_id=null   ):HasMany
   {
    return $this->dimensions()->where('type','row')
    ->where('broker_id',$broker_id)->
    where(function($query) use ($zone_id){
        
            $query->where('zone_id',$zone_id)
            ->orWhere('is_invariant',true);
        
    });
   }
}
