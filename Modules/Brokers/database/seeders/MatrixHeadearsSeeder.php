<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Brokers\Models\MatrixHeader;
use Modules\Brokers\Models\FormType;
use Modules\Translations\Models\Translation;


class MatrixHeadearsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MatrixHeader::insert($this->getChallengesColumnsHeadears());
        MatrixHeader::insert($this->getChallengesRowsHeadears());
        $this->translateChallengeColumns();
    }


    public function getChallengesRowsHeadears()
    {
        $challenges = [
            [
                'id'=>17,
                'type' => 'row',
                'title' => 'Equity Growth Target',
                'slug' => 'equity-growth-target',
                'group_name' => 'challenge',
                'description' => 'Equity Growth Target',
            ],
            [
                'id'=>18,
                'type' => 'row',
                'title' => 'Daily Drawdown Limit',
                'slug' => 'daily-drawdown-limit',
                'group_name' => 'challenge',
                'description' => 'Daily Drawdown Limit',
            ],
            [
                'id'=>19,
                'type' => 'row',
                'title' => 'Max Drawdown Limit',
                'slug' => 'max-drawdown-limit',
                'group_name' => 'challenge',
                'description' => 'Max Drawdown Limit',
            ],
            [
                'id'=>20,
                'type' => 'row',
                'title' => 'Inactivity Period',
                'slug' => 'inactivity-period',
                'group_name' => 'challenge',
                'description' => 'Inactivity Period',
            ],
            [
                'id'=>21,
                'type' => 'row',
                'title' => 'Leverage',
                'slug' => 'leverage',
                'group_name' => 'challenge',
                'description' => 'Leverage',
            ],
            [
                'id'=>22,
                'type' => 'row',
                'title' => 'Stop Loss Requirement',
                'slug' => 'stop-loss-requirement',
                'group_name' => 'challenge',
                'description' => 'Stop Loss Requirement',
            ],
            [
                'id'=>23,
                'type' => 'row',
                'title' => 'Positions Over Weekend',
                'slug' => 'positions-over-weekend',
                'group_name' => 'challenge',
                'description' => 'Positions Over Weekend',
            ],
            [
                'id'=>24,
                'type' => 'row',
                'title' => 'Max Time',
                'slug' => 'max-time',
                'group_name' => 'challenge',
                'description' => 'Max Time',
            ],
            [
                'id'=>25,
                'type' => 'row',
                'title' => 'Add-Ons',
                'slug' => 'add-ons',
                'group_name' => 'challenge',
                'description' => 'Add-Ons',
            ],
            [
                'id'=>26,
                'type' => 'row',
                'title' => 'Reward Based on equity Growth',
                'slug' => 'reward-based-on-equity-growth',
                'group_name' => 'challenge',
                'description' => 'Reward Based on equity Growth',
            ],
            [
                'id'=>27,
                'type' => 'row',
                'title' => 'Payout',
                'slug' => 'payout',
                'group_name' => 'challenge',
                'description' => 'Payout',
            ],
        ];
        return $challenges;
    }
    public function getChallengesColumnsHeadears()
    {
        $textType = FormType::where('name', 'Text')->first();
        $challenges = [
            [   
                'id'=>11,
                'type' => 'column',
                'title' => 'Funded Account',
                'slug' => 'step-0-funded-account',
                'group_name' => 'step-0',
                'form_type_id' => $textType->id,
            ],
            [
                'id'=>12,
                'type' => 'column',
                'title' => 'Funded Account',
                'slug' => 'step-1-funded-account',
                'group_name' => 'step-1',
                'form_type_id' => $textType->id,
            ],
            [
                'id'=>13,
                'type' => 'column',
                'title' => 'Step 1 Investiqa Assesments',
                'slug' => 'step-1-investiqa-assesments',
                'group_name' => 'step-1',
                'form_type_id' => $textType->id,
            ],
            [
                'id'=>14,
                'type' => 'column',
                'title' => 'Funded Account',
                'slug' => 'step-2-funded-account',
                'group_name' => 'step-2',
                'form_type_id' => $textType->id,
            ],
            [
                'id'=>15,
                'type' => 'column',
                'title' => 'Step 2 Investiqa Assesments',
                'slug' => 'step-2-investiqa-assesments',
                'group_name' => 'step-2',
                'form_type_id' => $textType->id,
            ],
            [
                'id'=>16,
                'type' => 'column',
                'title' => 'Step 2 Investiqa Confirmation',
                'slug' => 'step-2-investiqa-confirmation',
                'group_name' => 'step-2',
                'form_type_id' => $textType->id,
            ],
        ];
        return $challenges;
    }

    public function translateChallengeColumns()
    {
        Translation::insert([
            [
             "translation_type"=>"property",
             "property"=>"title",
             "value"=>"Ro Funded Account",
             "language_code"=>"ro",
             "translationable_id"=>11,
             "translationable_type"=>MatrixHeader::class
            ],
            [
                "translation_type"=>"property",
                "property"=>"title",
                "value"=>"Ro Funded Account",
                "language_code"=>"ro",
                "translationable_id"=>12,
                "translationable_type"=>MatrixHeader::class
            ],
            [
                "translation_type"=>"property",
                "property"=>"title",
                "value"=>"Ro Step 1 Investiqa Assesments",
                "language_code"=>"ro",
                "translationable_id"=>13,
                "translationable_type"=>MatrixHeader::class
            ],
            [
                "translation_type"=>"property",
                "property"=>"title",
                "value"=>"Ro Funded Account",
                "language_code"=>"ro",
                "translationable_id"=>14,
                "translationable_type"=>MatrixHeader::class
            ],
            [
                "translation_type"=>"property",
                "property"=>"title",
                "value"=>"Ro Step 2 Investiqa Assesments",
                "language_code"=>"ro",
                "translationable_id"=>15,
                "translationable_type"=>MatrixHeader::class
            ],
            [
                "translation_type"=>"property",
                "property"=>"title",
                "value"=>"Ro Step 2 Investiqa Confirmation",
                "language_code"=>"ro",
                "translationable_id"=>16,
                "translationable_type"=>MatrixHeader::class
            ],
        ]);
    }
}
