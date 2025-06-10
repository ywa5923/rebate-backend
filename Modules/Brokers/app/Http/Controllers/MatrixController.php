<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Brokers\Models\MatrixHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Brokers\Transformers\MatrixHeaderResource;
use Modules\Brokers\Services\MatrixHeadearsQueryParser;
use Modules\Brokers\Repositories\MatrixHeaderRepository;

class MatrixController extends Controller
{
    public function getHeaders(MatrixHeadearsQueryParser $queryParser, Request $request , MatrixHeaderRepository $rep)
    {
        $queryParser->parse($request);
        if (empty($queryParser->getWhereParams())) {
            return new Response("not found", 404);
        }
       
      
      
        $columnHeaders = $rep->getColumnHeaders(
            $queryParser->getWhereParam("matrix_id"),
            $queryParser->getWhereParam("broker_id")??null,
            $queryParser->getWhereParam("broker_id_strict")[2]??false
        );

        //return $columnHeaders;

        $rowHeaders = $rep->getRowHeaders(
            $queryParser->getWhereParam("matrix_id"),
            $queryParser->getWhereParam("broker_id")??null,
            $queryParser->getWhereParam("broker_id_strict")[2]??false
        );
       

        // $headers = MatrixHeader::with(['formType.items' => function($query) {
        //     $query->with(['dropdown' => function($q) {
        //         $q->with('dropdownOptions');
        //     }]);
        // }])->get();
        //$headers = MatrixHeader::with('formType.items.dropdown.dropdownOptions')->get();
       // DB::enableQueryLog();
        
        // $headers = MatrixHeader::with('formType.items.dropdown.dropdownOptions')
        //     ->where(function ($query) use ($broker_id) {
        //         $query->whereNull('broker_id')
        //             ->orWhere('broker_id', $broker_id);
        //     })
        //     ->where('type', $headerType)
        //     ->get();

      
        return [
            'columnHeaders' => MatrixHeaderResource::collection($columnHeaders),
            'rowHeaders' => MatrixHeaderResource::collection($rowHeaders)
        ];
        //return MatrixHeaderResource::collection($columnHeaders);


        // return $headers->map(function($header) {
        //     $items = $header->formType->items->map(function($item) {
        //         $formattedItem = [
        //             'name' => $item->name,
        //             'type' => $item->type,
        //             'placeholder' => $item->placeholder
        //         ];

        //         if ($item->type === 'select' && $item->dropdown) {
        //             $formattedItem['options'] = $item->dropdown->dropdownOptions->map(function($option) {
        //                 return [
        //                     'value' => $option->value,
        //                     'label' => $option->label
        //                 ];
        //             });
        //         }

        //         return $formattedItem;
        //     });

        //     return [
        //         'name' => $header->name,
        //         'form_type' => [
        //             'type' => $header->formType->name,
        //             'items' => $items
        //         ]
        //     ];
        // });
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('brokers::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('brokers::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('brokers::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('brokers::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
