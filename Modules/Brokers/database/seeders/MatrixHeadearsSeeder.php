<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Brokers\Models\FormType;
use Modules\Brokers\Models\MatrixHeader;
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
                'id' => 17,
                'type' => 'row',
                'title' => 'Profit Target',
                'is_percentage' => true,
                'broker_can_see' => true,
                'percentage_value' => 10,
                'slug' => 'profit-target',
                'group_name' => 'challenge',
                'description' => 'Profit amount the client needs to achieve in order to pass to the next step of the program and be eligible to receive the payout',
            ],
            [
                'id' => 18,
                'type' => 'row',
                'title' => 'Maximum Daily Loss',
                'is_percentage' => true,
                'broker_can_see' => true,
                'percentage_value' => 4,
                'slug' => 'maximum-daily-loss',
                'group_name' => 'challenge',
                'description' => 'Maximum Daily Loss is the maximum amount a client account can lose in any given day',
            ],
            [
                'id' => 19,
                'type' => 'row',
                'title' => 'Maximum Total Loss',
                'is_percentage' => true,
                'broker_can_see' => true,
                'percentage_value' => 8,
                'slug' => 'maximum-total-loss',
                'group_name' => 'challenge',
                'description' => 'Maximum Total Loss is the amount your equity or balance can\'t go below',
            ],

            [
                'id' => 20,
                'type' => 'row',
                'title' => 'Leverage',
                'is_percentage' => false,
                'broker_can_see' => true,
                'percentage_value' => null,
                'slug' => 'leverage',
                'group_name' => 'challenge',
                'description' => 'Maximum leverage a client account can have',
            ],
            [
                'id' => 21,
                'type' => 'row',
                'title' => 'Consistency on Rewards ',
                'is_percentage' => false,
                'broker_can_see' => true,
                'percentage_value' => null,
                'slug' => 'consistency-on-rewards',
                'group_name' => 'challenge',
                'description' => 'Best trading day profit cannot exceed the total profit with a specific %',
            ],

            [
                'id' => 22,
                'type' => 'row',
                'title' => 'Minimum Trading Days',
                'is_percentage' => false,
                'broker_can_see' => true,
                'percentage_value' => null,
                'slug' => 'minimum-trading-days',
                'group_name' => 'challenge',
                'description' => 'Minimum number of days the client has to trade',
            ],
            [
                'id' => 23,
                'type' => 'row',
                'title' => 'Add-Ons',
                'is_percentage' => false,
                'broker_can_see' => true,
                'percentage_value' => null,
                'slug' => 'add-ons',
                'group_name' => 'challenge',
                'description' => 'Any add-ons that can enhace the client challenge experience',
            ],
            [
                'id' => 24,
                'type' => 'row',
                'title' => 'Refundable Fee',
                'is_percentage' => false,
                'broker_can_see' => true,
                'percentage_value' => null,
                'slug' => 'refundable-fee',
                'group_name' => 'challenge',
                'description' => 'The evaluation fee is reimbursed with the first payout, once the client become a funded trader',
            ],
            [
                'id' => 25,
                'type' => 'row',
                'title' => 'Profit Split',
                'is_percentage' => false,
                'broker_can_see' => true,
                'percentage_value' => null,
                'slug' => 'profit-split',
                'group_name' => 'challenge',
                'description' => 'Profit split value between the client and the prop firm',
            ],
            [
                'id' => 26,
                'type' => 'row',
                'title' => 'Payout',
                'is_percentage' => false,
                'broker_can_see' => true,
                'percentage_value' => null,
                'slug' => 'payout',
                'group_name' => 'challenge',
                'description' => 'Frequency a client can get his payments',
            ],
            [
                'id' => 27,
                'type' => 'row',
                'title' => 'Evaluation Cost',
                'is_percentage' => false,
                'broker_can_see' => true,
                'percentage_value' => null,
                'slug' => 'evaluation-cost',
                'group_name' => 'challenge',
                'description' => 'Purchase cost of a challenge',
            ],
            [
                'id' => 28,
                'type' => 'row',
                'title' => 'Promo Code',
                'is_percentage' => false,
                'broker_can_see' => true,
                'percentage_value' => null,
                'slug' => 'promo-code',
                'group_name' => 'challenge',
                'description' => 'Dedicated discount code for FXRebate applied to all challenges (if exist)',
            ],
            [
                'id' => 29,
                'type' => 'row',
                'title' => 'Affiliate Commission',
                'is_percentage' => false,
                'broker_can_see' => true,
                'percentage_value' => null,
                'slug' => 'affiliate-commission',
                'group_name' => 'challenge',
                'description' => 'What is the % prop firm pays FXRebate as an affiliate',
            ],
            [
                'id' => 30,
                'type' => 'row',
                'title' => 'Client Rebate',
                'is_percentage' => false,
                'broker_can_see' => false,
                'percentage_value' => null,
                'slug' => 'client-rebate',
                'group_name' => 'challenge',
                'description' => 'To be able to receive challenge purchase rebates link your trading account to your FXRebate profile',
            ],

        ];

        return $challenges;
    }

    public function getChallengesColumnsHeadears()
    {
        $textType = FormType::where('name', 'Text')->first();
        $challenges = [
            [
                'id' => 11,
                'type' => 'column',
                'title' => 'Funded Account',
                'slug' => 'step-0-funded-account',
                'group_name' => 'step-0',
                'form_type_id' => $textType->id,
            ],
            [
                'id' => 12,
                'type' => 'column',
                'title' => 'Funded Account',
                'slug' => 'step-1-funded-account',
                'group_name' => 'step-1',
                'form_type_id' => $textType->id,
            ],
            [
                'id' => 13,
                'type' => 'column',
                'title' => 'Step 1 Investiqa Assesments',
                'slug' => 'step-1-investiqa-assesments',
                'group_name' => 'step-1',
                'form_type_id' => $textType->id,
            ],
            [
                'id' => 14,
                'type' => 'column',
                'title' => 'Funded Account',
                'slug' => 'step-2-funded-account',
                'group_name' => 'step-2',
                'form_type_id' => $textType->id,
            ],
            [
                'id' => 15,
                'type' => 'column',
                'title' => 'Step 2 Investiqa Assesments',
                'slug' => 'step-2-investiqa-assesments',
                'group_name' => 'step-2',
                'form_type_id' => $textType->id,
            ],
            [
                'id' => 16,
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
                'translation_type' => 'property',
                'property' => 'title',
                'value' => 'Ro Funded Account',
                'language_code' => 'ro',
                'translationable_id' => 11,
                'translationable_type' => MatrixHeader::class,
            ],
            [
                'translation_type' => 'property',
                'property' => 'title',
                'value' => 'Ro Funded Account',
                'language_code' => 'ro',
                'translationable_id' => 12,
                'translationable_type' => MatrixHeader::class,
            ],
            [
                'translation_type' => 'property',
                'property' => 'title',
                'value' => 'Ro Step 1 Investiqa Assesments',
                'language_code' => 'ro',
                'translationable_id' => 13,
                'translationable_type' => MatrixHeader::class,
            ],
            [
                'translation_type' => 'property',
                'property' => 'title',
                'value' => 'Ro Funded Account',
                'language_code' => 'ro',
                'translationable_id' => 14,
                'translationable_type' => MatrixHeader::class,
            ],
            [
                'translation_type' => 'property',
                'property' => 'title',
                'value' => 'Ro Step 2 Investiqa Assesments',
                'language_code' => 'ro',
                'translationable_id' => 15,
                'translationable_type' => MatrixHeader::class,
            ],
            [
                'translation_type' => 'property',
                'property' => 'title',
                'value' => 'Ro Step 2 Investiqa Confirmation',
                'language_code' => 'ro',
                'translationable_id' => 16,
                'translationable_type' => MatrixHeader::class,
            ],
        ]);
    }
}
