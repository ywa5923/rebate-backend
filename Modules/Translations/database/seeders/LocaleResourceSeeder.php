<?php

namespace Modules\Translations\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Translations\Models\LocaleResource;
use Modules\Translations\Models\Translation;

class LocaleResourceSeeder extends Seeder
{
    //php artisan db:seed --class=\\Modules\\Brokers\\Database\\Seeders\\LocaleResourceSeeder
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $localeResourceId=LocaleResource::insertGetId([
            "key"=>"page_brokers",
            "section"=>"server",
            "zone_code"=>"eu",
            "json_content"=>json_encode($this->getServerComponentEnData())
        ]);

        Translation::insert([
            "translationable_type"=>LocaleResource::class,
            "translationable_id"=> $localeResourceId,
            "language_code"=>"ro",
            "translation_type"=>"property",
            "property"=> "json_content",
            "value"=>json_encode($this->getServerComponentRoData())
        ]);

        $localeResourceId=LocaleResource::insertGetId([
            "key"=>"page_brokers",
            "section"=>"client",
            "is_invariant"=>1,
            "json_content"=>json_encode($this->getClientComponentEnData())
        ]);

        Translation::insert([
            "translationable_type"=>LocaleResource::class,
            "translationable_id"=> $localeResourceId,
            "language_code"=>"ro",
            "translation_type"=>"property",
            "property"=> "json_content",
            "value"=>json_encode($this->getClientComponentRoData())
        ]);
        
    }

    public function getServerComponentEnData()
    {
        return [
            "title_test"=>"Title Test",
            "content_test"=>"Content Test"
        ];
    }

    public function getServerComponentRoData()
    {
        return [
            "title_test"=>"Titlu Test",
            "content_test"=>"Conteniu Test"
        ];
    }

    public function getClientComponentEnData(){
        return [
            "title_test"=>"Title Test",
            "content_test"=>"Content Test"
        ];
    }

    public function getClientComponentRoData(){
        return [
            "title_test"=>"Titlu Test",
            "content_test"=>"Conteniu Test"
        ];
    }
}
