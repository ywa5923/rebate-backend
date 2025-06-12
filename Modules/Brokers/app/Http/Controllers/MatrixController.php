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
use Modules\Brokers\Models\Matrix;
use Modules\Brokers\Models\MatrixDimension;
use Modules\Brokers\Models\MatrixValue;
use Modules\Brokers\Models\MatrixHeaderOption;
class MatrixController extends Controller
{
    public function getHeaders(MatrixHeadearsQueryParser $queryParser, Request $request, MatrixHeaderRepository $rep)
    {
        $queryParser->parse($request);
        if (empty($queryParser->getWhereParams())) {
            return new Response("not found", 404);
        }



        $columnHeaders = $rep->getColumnHeaders(
            $queryParser->getWhereParam("matrix_id"),
            $queryParser->getWhereParam("broker_id") ?? null,
            $queryParser->getWhereParam("broker_id_strict")[2] ?? false
        );

        //return $columnHeaders;

        $rowHeaders = $rep->getRowHeaders(
            $queryParser->getWhereParam("matrix_id"),
            $queryParser->getWhereParam("broker_id") ?? null,
            $queryParser->getWhereParam("broker_id_strict")[2] ?? false
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

    public function store(Request $request, MatrixHeaderRepository $rep)
    {
       
        $startTime = microtime(true);

        $data = $request->validate([
            'matrix' => 'required|array',
            'broker_id' => 'required|integer',
            'matrix_id' => 'required|string',
        ]);

        $matrixName = $data['matrix_id'];
        $brokerId = $data['broker_id'];
        $allHeaders = $rep->getAllHeaders(["name", "=", $matrixName], ["broker_id", "=", $brokerId]);
        $matrix = Matrix::where("name", "=", $matrixName)->first();

        //flush matrix data first
        $rep->flushMatrix($matrix->id, $brokerId);

        // Prepare bulk insert data
        $rowDimensions = [];
        $columnDimensions = [];
        $matrixValues = [];
        $rowHeaderSubOptionsIds = [];
        foreach ($data['matrix'] as $rowIndex => $row) {
           
            $selectedRowHeaderSubOptions = $row[0]['selectedRowHeaderSubOptions'] ?? null;

            // "selectedRowHeaderSubOptions":[{"value":"row-subheader-1","label":"Row subheader 1"},{"value":"row-subheader-2","label":"Row subheader 2"}]
            $rowHeaderSlug = $row[0]['rowHeader'];
            $rowHeaderId = $this->getHeaderId($rowHeaderSlug, $allHeaders);
           
            if ($rowHeaderId == null) {
                $rowHeaderId = MatrixHeader::insertGetId([
                    'title' => ucwords(str_replace('-', ' ', $rowHeaderSlug)),
                    'slug' => $rowHeaderSlug,
                    'broker_id' => $brokerId,
                    'type' => 'row',
                    'matrix_id' => $matrix->id
                ]);

                $rowHeaderSubOptions = [];
                foreach ($selectedRowHeaderSubOptions as $subOption) {
                    $rowHeaderSubOptions[] = [
                        'parent_id' => $rowHeaderId,
                        'slug' => $subOption['value'],
                        'title' => $subOption['label'],
                        'broker_id' => $brokerId,
                        'type' => 'row',
                        'matrix_id' => $matrix->id
                    ];
                }
                MatrixHeader::insert($rowHeaderSubOptions);
                $rowHeaderSubOptionsIds[$rowHeaderId] = MatrixHeader::where(
                    ['parent_id', $rowHeaderId],
                    ['broker_id', $brokerId],
                    ['matrix_id', $matrix->id],
                    ['type', 'row']
                    )->pluck('id')->toArray();
                
            } else {

                //Save row header sub options in matrix_header table and get their ids
                //save them in broker_instruments table

                foreach ($selectedRowHeaderSubOptions as $subOption) {
                    $id = $this->getHeaderId($subOption['value'], $allHeaders);
                   
                    
                    if ($id != null) {
                        $rowHeaderSubOptionsIds[$rowHeaderId][] = $id;
                    } else {
                        $rowHeaderSubOptionsIds[$rowHeaderId][] = MatrixHeader::insertGetId([
                            'parent_id' => $rowHeaderId,
                            'slug' => $subOption['value'],
                            'title' => $subOption['label'],
                            'broker_id' => $brokerId,
                            'type' => 'row',
                            'matrix_id' => $matrix->id
                        ]);
                    }
                }
            }

            
           

            // Add row dimension
            $rowDimensions[] = [
                'matrix_id' => $matrix->id,
                'broker_id' => $brokerId,
                'order' => $rowIndex,
                'matrix_header_id' => $rowHeaderId,
                'type' => 'row'
            ];

            foreach ($row as $cellIndex => $cell) {
                $colHeaderSlug = $cell['colHeader'];
                $colHeaderId = $this->getHeaderId($colHeaderSlug, $allHeaders);

                if(!isset($columnDimensions[$cellIndex])){
                    $columnDimensions[$cellIndex] = [
                        'matrix_id' => $matrix->id,
                        'broker_id' => $brokerId,
                        'order' => $cellIndex,
                        'matrix_header_id' => $colHeaderId,
                        'type' => 'column'
                    ];
                }

                if($colHeaderId == null){

                 throw new \Exception("Column header not found");
                }

                // Store value data for later bulk insert
                $matrixValues[] = [
                    'matrix_id' => $matrix->id,
                    'broker_id' => $brokerId,
                    'row_index' => $rowIndex,
                    'col_index' => $cellIndex,
                    'value' => json_encode($cell['value'])
                ];
            }
        }

       // dd(array_values($columnDimensions));
        // Bulk insert dimensions and get their IDs
        MatrixDimension::insert($rowDimensions);
        MatrixDimension::insert(array_values($columnDimensions));

        //Get the inserted IDs
        $rowDimIds = MatrixDimension::where('matrix_id', $matrix->id)
            ->where('type', 'row')
            ->where('broker_id', $brokerId)
            ->orderBy('order')
            ->pluck('id')
            ->toArray();

        $colDimIds = MatrixDimension::where('matrix_id', $matrix->id)
            ->where('type', 'column')
            ->where('broker_id', $brokerId)
            ->orderBy('order')
            ->pluck('id')
            ->toArray();

        // Update matrix values with correct dimension IDs
        foreach ($matrixValues as &$value) {
            $value['matrix_row_id'] = $rowDimIds[$value['row_index']];
            $value['matrix_column_id'] = $colDimIds[$value['col_index']];
            unset($value['row_index'], $value['col_index']);
        }

        // Bulk insert values
        MatrixValue::insert($matrixValues);

        foreach ($rowHeaderSubOptionsIds as $rowHeaderId => $subOptionIds) {
            $data=[];
            foreach ($subOptionIds as $subOptionId) {
                $data[]=[
                    'matrix_id' => $matrix->id,
                    'broker_id' => $brokerId,
                    'matrix_header_id' => $rowHeaderId,
                    'sub_option_id' => $subOptionId,
                ];
            }
            MatrixHeaderOption::insert($data);
        }

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        return response()->json([
            'message' => 'Matrix data saved successfully',
            'performance' => [
                'execution_time_ms' => $executionTime,
                'rows_count' => count($rowDimensions),
                'columns_count' => count($columnDimensions),
                'values_count' => count($matrixValues)
            ]
        ]);
    }

    public function getHeaderId($headerSlug, $headers)
    {
        $header = $headers->firstWhere('slug', $headerSlug);
        return $header ? $header->id : null;
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
// [
//     [
//         {
//             "value": [],
//             "rowHeader": "row-header-2",
//             "colHeader": "trade-mt4",
//             "type": "Text",
//             "selectedRowHeaderSubOptions": [
//                 {
//                     "value": "row-subheader-1",
//                     "label": "Row subheader 1"
//                 },
//                 {
//                     "value": "row-subheader-2",
//                     "label": "Row subheader 2"
//                 }
//             ]
//         },
//         {
//             "value": {
//                 "Number": "jyykyk"
//             },
//             "rowHeader": "row-header-1",
//             "colHeader": "zero-mt4",
//             "type": "Number"
//         },
//         {
//             "value": {
//                 "Number": "34",
//                 "Currency": "lots"
//             },
//             "rowHeader": "row-header-1",
//             "colHeader": "trade-mt5",
//             "type": "NumberWithCurrency"
//         }
//     ]
// ]