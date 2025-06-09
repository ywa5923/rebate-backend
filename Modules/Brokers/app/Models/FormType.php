<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class FormType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function items()
    {
        return $this->belongsToMany(FormItem::class, 'form_type_form_item');
    }

    public function matrixHeaders()
    {
        return $this->hasMany(MatrixHeader::class);
    }


}
