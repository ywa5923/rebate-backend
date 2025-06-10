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
       $matrixId= Matrix::insertGetId([
            
            'name'=>'Matrix 1',
            'description'=>'Matrix 1 Description'
           
        ]);

        MatrixHeader::insert([[
            'id'=>1,
            'matrix_id'=>1,
            'title'=>'Trade.MT4',
            'type'=>'column',
            'slug'=>'trade-mt4',
            'zone_id'=>1,
            'is_invariant'=>true,
            'parent_id'=>null,
            'form_type_id'=>1,
            'broker_id'=>1,
        ],[
            'id'=>2,
            'matrix_id'=>1,
            'title'=>'Zero.MT4',
            'type'=>'column',
            'slug'=>'zero-mt4',
            'zone_id'=>1,
            'is_invariant'=>true,
            'parent_id'=>null,
            'form_type_id'=>2,
            'broker_id'=>null,
            ],
            [
                'id'=>3,
                'matrix_id'=>1,
                'title'=>'Zero.MT5',
                'type'=>'column',
                'slug'=>'zero-mt5',
                'zone_id'=>1,
                'is_invariant'=>true,
                'parent_id'=>null,
                'form_type_id'=>3,
                'broker_id'=>2,
            ],
            [
                'id'=>4,
                'matrix_id'=>1,
                'title'=>'Trade.MT5',
                'type'=>'column',
                'slug'=>'trade-mt5',
                'zone_id'=>1,
                'is_invariant'=>true,
                'parent_id'=>null,
                'form_type_id'=>1,
                'broker_id'=>1,
            ],
            [
                'id'=>5,
                'matrix_id'=>1,
                'title'=>'Row header 1',
                'type'=>'row',
                'slug'=>'row-header-1',
                'zone_id'=>1,
                'is_invariant'=>true,
                'parent_id'=>null,
                'form_type_id'=>null,
                'broker_id'=>null,
            ],
            [
                'id'=>6,
                'matrix_id'=>1,
                'title'=>'Row header 2',
                'type'=>'row',
                'slug'=>'row-header-2',
                'zone_id'=>1,
                'is_invariant'=>true,
                'parent_id'=>null,
                'form_type_id'=>null,
                'broker_id'=>null,
            ],
            [
                'id'=>7,
                'matrix_id'=>1,
                'title'=>'Row subheader 1',
                'type'=>'row',
                'slug'=>'row-subheader-1',
                'zone_id'=>1,
                'is_invariant'=>true,
                'parent_id'=>5,
                'form_type_id'=>null,
                'broker_id'=>null,
            ],  
            [
                'id'=>8,
                'matrix_id'=>1,
                'title'=>'Row subheader 2',
                'type'=>'row',
                'slug'=>'row-subheader-2',
                'zone_id'=>1,
                'is_invariant'=>true,
                'parent_id'=>5,
                'form_type_id'=>null,
                'broker_id'=>null,
            ],  
            [
                'id'=>9,
                'matrix_id'=>1,
                'title'=>'Row subheader 3',
                'type'=>'row',
                'slug'=>'row-subheader-3',
                'zone_id'=>1,
                'is_invariant'=>true,
                'parent_id'=>6,
                'form_type_id'=>null,
                'broker_id'=>null,
            ],  
            [
                'id'=>10,
                'matrix_id'=>1,
                'title'=>'Row subheader 4',
                'type'=>'row',
                'slug'=>'row-subheader-4',
                'zone_id'=>1,
                'is_invariant'=>true,
                'parent_id'=>6,
                'form_type_id'=>null,
                'broker_id'=>null,
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
        
    ]);//end of header translations


}    
}        
       

