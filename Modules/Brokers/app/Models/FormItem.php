<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;

use Modules\Brokers\Database\Factories\FormItemFactory;
use Modules\Brokers\Models\DropdownCategory;

class FormItem extends Model
{
    

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];



    public function dropdown()
    {
        return $this->belongsTo(DropdownCategory::class, 'dropdown_id', 'id');
    }

    public function formTypes()
    {
        return $this->belongsToMany(FormType::class, 'form_type_form_item');
    }
}
