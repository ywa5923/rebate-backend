<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Tables\TableConfigInterface;

class  BaseRequest extends FormRequest
{
    protected TableConfigInterface $tableConfig;

   
    public function __construct()
    {
        parent::__construct();
    }

    

}
