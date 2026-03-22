<?php

namespace Modules\Brokers\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Utilities\ModelHelper;


class DynamicTableController extends Controller
{
    public function index(Request $request,int $broker_id, string $model)
    {
        $modelClass = ModelHelper::getModelClassFromSlug($model);
        return response()->json([
            'success' => true,
            'data' => 'Hello World'
        ]);
    }
}