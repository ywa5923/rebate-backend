<?php

namespace Modules\Brokers\Database\Seeders;
use Modules\Brokers\Models\Matrix;
use Modules\Brokers\Models\MatrixHeader;
use Modules\Brokers\Models\MatrixDimension;
use Modules\Brokers\Models\MatrixValue;
use Illuminate\Database\Seeder;
use Modules\Translations\Database\Seeders\TranslationsDatabaseSeeder;
use Modules\Translations\Models\Translation;

class MatrixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Matrix::create([
            'id'=>1,
            'name'=>'Matrix 1',
            'description'=>'Matrix 1 Description'
           
        ]);

        MatrixHeader::insert([[
            'id'=>1,
            'matrix_id'=>1,
            'title'=>'Trade.MT4',
            'type'=>'column',
            'description'=>'Header 1 Description',
            'zone_id'=>1,
            'is_invariant'=>true,
        ],[
            'id'=>2,
            'matrix_id'=>1,
            'title'=>'Zero.MT4',
            'type'=>'column',
            'description'=>'Header 2 Description',
            'zone_id'=>1,
            'is_invariant'=>true,
            ],
            [
                'id'=>3,
                'matrix_id'=>1,
                'title'=>'Zero.MT5',
                'type'=>'column',
                'description'=>'Header 3 Description',
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>4,
                'matrix_id'=>1,
                'title'=>'Trade.MT5',
                'type'=>'column',
                'description'=>'Header 3 Description',
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>5,
                'matrix_id'=>1,
                'title'=>'Commission',
                'type'=>'row',
                'description'=>'Header 3 Description',
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>6,
                'matrix_id'=>1,
                'title'=>'Maximum Leverage',
                'type'=>'row',
                'description'=>'Header 3 Description',
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>7,
                'matrix_id'=>1,
                'title'=>'Mobile Platform',
                'type'=>'row',
                'description'=>'Header 3 Description',
                'zone_id'=>null,
                'is_invariant'=>true,
            ],  
            [
                'id'=>8,
                'matrix_id'=>1,
                'title'=>'Trading Platform',
                'type'=>'row',
                'description'=>'Header 3 Description',
                'zone_id'=>null,
                'is_invariant'=>true,
            ],  
            [
                'id'=>9,
                'matrix_id'=>1,
                'title'=>'Spread type',
                'type'=>'row',
                'description'=>'Header 3 Description',
                'zone_id'=>null,
                'is_invariant'=>true,
            ],  
            [
                'id'=>10,
                'matrix_id'=>1,
                'title'=>'Minimum Deposit',
                'type'=>'row',
                'description'=>'Header 3 Description',
                'zone_id'=>null,
                'is_invariant'=>true,
            ],  
            [
                'id'=>11,
                'matrix_id'=>1,
                'title'=>'Trailling Stops',
                'type'=>'row',
                'description'=>'Header 3 Description',
                'zone_id'=>null,
                'is_invariant'=>true,
            ],  
            [
                'id'=>12,
                'matrix_id'=>1,
                'title'=>'Scallping Alowed',
                'type'=>'row',
                'description'=>'Header 3 Description',
                'zone_id'=>null,
                'is_invariant'=>true,
            ], 
    ]);//end of matrix header

    //Add header translations
    Translation::insert([
        [
         "translation_type"=>"property",
         "property"=>"title",
         "value"=>"Ro Trade.MT4",
         "language_code"=>"ro",
         "translationable_id"=>1,
         "translationable_type"=>MatrixHeader::class
        ],
        [
            "translation_type"=>"property",
            "property"=>"title",
            "value"=>"Ro Zero.MT4",
            "language_code"=>"ro",
            "translationable_id"=>2,
            "translationable_type"=>MatrixHeader::class
        ],
        [
            "translation_type"=>"property",
            "property"=>"title",
            "value"=>"Ro Zero.MT5",
            "language_code"=>"ro",
            "translationable_id"=>3,
            "translationable_type"=>MatrixHeader::class
        ],
        [
            "translation_type"=>"property",
            "property"=>"title",
            "value"=>"Ro Trade.MT5",
            "language_code"=>"ro",
             "translationable_id"=>4,
            "translationable_type"=>MatrixHeader::class
        ],
        [
            "translation_type"=>"property",
            "property"=>"title",
            "value"=>"Ro Commission",
            "language_code"=>"ro",
            "translationable_id"=>5,
                "translationable_type"=>MatrixHeader::class
        ],
        [
            "translation_type"=>"property",
            "property"=>"title",
            "value"=>"Ro Maximum Leverage",
            "language_code"=>"ro",
            "translationable_id"=>6,
            "translationable_type"=>MatrixHeader::class
        ],
        [
            "translation_type"=>"property",
            "property"=>"title",
            "value"=>"Ro Mobile Platform",
            "language_code"=>"ro",
            "translationable_id"=>7,
            "translationable_type"=>MatrixHeader::class
        ],
        [
            "translation_type"=>"property",
            "property"=>"title",
            "value"=>"Ro Trading Platform",
            "language_code"=>"ro",
             "translationable_id"=>8,
            "translationable_type"=>MatrixHeader::class
        ],
        [
            "translation_type"=>"property",
            "property"=>"title",
            "value"=>"Ro Spread Type",
            "language_code"=>"ro",
            "translationable_id"=>9,
            "translationable_type"=>MatrixHeader::class
        ],
        [
            "translation_type"=>"property",
            "property"=>"title",
            "value"=>"Ro Minimum Deposit",
            "language_code"=>"ro",
            "translationable_id"=>10,
            "translationable_type"=>MatrixHeader::class
        ],
        [
            "translation_type"=>"property",
            "property"=>"title",
            "value"=>"Ro Trailling Stops",
            "language_code"=>"ro",
            "translationable_id"=>11,
            "translationable_type"=>MatrixHeader::class
        ],
        [
            "translation_type"=>"property",
            "property"=>"title",
            "value"=>"Ro Scallping Alowed",
            "language_code"=>"ro",
            "translationable_id"=>12,
            "translationable_type"=>MatrixHeader::class
        ]
    ]);

        MatrixDimension::insert([
            [
                'id'=>1,
                'matrix_id'=>1,
                'matrix_header_id'=>1,
               // 'description'=>'trade.mt4',
                'type'=>'column',
                'order'=>1,
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>2,
                'matrix_id'=>1,
                'matrix_header_id'=>2,
                //'description'=>'zero.mt4',
                'type'=>'column',
                'order'=>2,
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>3,
                'matrix_id'=>1,
                'matrix_header_id'=>3,
                //'description'=>'zero.mt5',
                'type'=>'column',
                'order'=>4,
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>4,
                'matrix_id'=>1,
                'matrix_header_id'=>4,  
                //'description'=>'trade.mt5', 
                'type'=>'column',
                'order'=>3,
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>5,
                'matrix_id'=>1,
                'matrix_header_id'=>5,
                //'description'=>'Commission',
                'type'=>'row',
                'order'=>1,                 
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>6,
                'matrix_id'=>1,
                'matrix_header_id'=>6,
                //'description'=>'Maximum Leverage',
                'type'=>'row',
                'order'=>2,
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,   
            ],
            [
                'id'=>7,
                'matrix_id'=>1,
                'matrix_header_id'=>7,
                //'description'=>'Mobile Platform',
                'type'=>'row',
                'order'=>3,
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,

            ],
            [
                'id'=>8,
                'matrix_id'=>1,
                'matrix_header_id'=>8,
                //'description'=>'Trading Platform',
                'type'=>'row',
                'order'=>4,
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>9,
                'matrix_id'=>1,
                'matrix_header_id'=>9,
                //'description'=>'Spread type',
                'type'=>'row',
                'order'=>5,
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>10,
                'matrix_id'=>1,
                'matrix_header_id'=>10,
                //'description'=>'Minimum Deposit',
                'type'=>'row',
                'order'=>6,
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>11,
                'matrix_id'=>1,
                'matrix_header_id'=>11,
                //'description'=>'Trailling Stops',
                'type'=>'row',
                'order'=>7,
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>12,
                'matrix_id'=>1,
                'matrix_header_id'=>12,
                //sss'description'=>'Scallping Alowed',
                'type'=>'row',
                'order'=>8,
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ]
        ]); //end of matrix dimension

        MatrixValue::insert([
            [
                'id'=>1,
                'matrix_id'=>1,
                'matrix_column_id'=>1,
                'matrix_row_id'=>5,
                'value'=>'',
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>2,
                'matrix_id'=>1,
                'matrix_column_id'=>2,
                'matrix_row_id'=>5,
                'value'=>'$3 per side lot',
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>3,
                'matrix_id'=>1,
                'matrix_column_id'=>4,
                'matrix_row_id'=>5,
                'value'=>'',
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>4,
                'matrix_id'=>1,
                'matrix_column_id'=>3,
                'matrix_row_id'=>5,
                'value'=>'$3 per side lot',
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>5,
                'matrix_id'=>1,
                'matrix_column_id'=>1,
                'matrix_row_id'=>6,
                'value'=>'1000:1',
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>6,
                'matrix_id'=>1,
                'matrix_column_id'=>2,
                'matrix_row_id'=>6,
                'value'=>'1000:1',
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [   
                'id'=>7,
                'matrix_id'=>1,
                'matrix_column_id'=>4,
                'matrix_row_id'=>6,
                'value'=>'1000:1',
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ],
            [
                'id'=>8,
                'matrix_id'=>1,
                'matrix_column_id'=>3,
                'matrix_row_id'=>6,
                'value'=>'1000:1',
                'broker_id'=>1,
                'zone_id'=>null,
                'is_invariant'=>true,
            ]
            
            
        ]); //end of matrix value
    }
}
