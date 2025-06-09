<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Brokers\Models\DropdownOption;
use Modules\Brokers\Models\BrokerOption;

class DropdownCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    
    public function dropdownOptions()
    {
        return $this->hasMany(DropdownOption::class);
    }

    public function brokerOptions()
    {
        return $this->hasMany(BrokerOption::class);
    }
}
