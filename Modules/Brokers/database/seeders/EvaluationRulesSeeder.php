<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Brokers\Models\EvaluationRule;
use Modules\Brokers\Models\EvaluationOption;
class EvaluationRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $this->call([]);
        $evaluationRules = $this->getEvaluationRules();
        EvaluationRule::insert($evaluationRules);
        $evaluationOptions = $this->getEvaluationOptions();
        EvaluationOption::insert($evaluationOptions);
    }

    public function getEvaluationOptions()
    {
        $evaluationOptions = [
            // Rule 1: News Trading
            ['evaluation_rule_id' => 1, 'option_label' => 'Allowed', 'option_value' => 'allowed', 'is_getter' => false, 'description' => 'News trading is allowed'],
            ['evaluation_rule_id' => 1, 'option_label' => 'Not Allowed', 'option_value' => 'not_allowed', 'is_getter' => false, 'description' => 'News trading is not allowed'],
        
            // Rule 2: Copy Trading
            ['evaluation_rule_id' => 2, 'option_label' => 'Allowed', 'option_value' => 'allowed', 'is_getter' => false, 'description' => 'Copy Trading is allowed'],
            ['evaluation_rule_id' => 2, 'option_label' => 'Not Allowed', 'option_value' => 'not_allowed', 'is_getter' => false, 'description' => 'Copy Trading is not allowed'],
        
            // Rule 3: Scalping
            ['evaluation_rule_id' => 3, 'option_label' => 'Allowed', 'option_value' => 'allowed', 'is_getter' => false, 'description' => 'Scalping Trading is allowed'],
            ['evaluation_rule_id' => 3, 'option_label' => 'Not Allowed', 'option_value' => 'not_allowed', 'is_getter' => false, 'description' => 'Scalping Trading is not allowed'],
        
            // Rule 4: Hedging
            ['evaluation_rule_id' => 4, 'option_label' => 'Allowed', 'option_value' => 'allowed', 'is_getter' => false, 'description' => 'Hedging Operation in the same account is allowed'],
            ['evaluation_rule_id' => 4, 'option_label' => 'Not Allowed', 'option_value' => 'not_allowed', 'is_getter' => false, 'description' => 'Hedging Operation in the same account is not allowed'],
        
            // Rule 5: Expert Advisors (EAs)
            ['evaluation_rule_id' => 5, 'option_label' => 'Allowed', 'option_value' => 'allowed', 'is_getter' => false, 'description' => 'Expert Advisors (EAs) usage is allowed'],
            ['evaluation_rule_id' => 5, 'option_label' => 'Not Allowed', 'option_value' => 'not_allowed', 'is_getter' => false, 'description' => 'Expert Advisors (EAs) usage is not allowed'],
        
            // Rule 6: Position Over Weekend
            ['evaluation_rule_id' => 6, 'option_label' => 'Allowed', 'option_value' => 'allowed', 'is_getter' => false, 'description' => 'Positions over weekend is allowed'],
            ['evaluation_rule_id' => 6, 'option_label' => 'Not Allowed', 'option_value' => 'not_allowed', 'is_getter' => false, 'description' => 'Positions over weekend is not allowed'],
            ['evaluation_rule_id' => 6, 'option_label' => 'With Add-On', 'option_value' => 'with_add_on', 'is_getter' => false, 'description' => 'Positions over weekend are available with add-on purchase'],
        
            // Rule 7: All Trading Strategies
            ['evaluation_rule_id' => 7, 'option_label' => 'Allowed', 'option_value' => 'allowed', 'is_getter' => false, 'description' => 'All Trading Strategies are allowed'],
            ['evaluation_rule_id' => 7, 'option_label' => 'Not Allowed', 'option_value' => 'not_allowed', 'is_getter' => true, 'description' => 'Some Trading Strategies are not allowed (like reverse arbitrage, arbitrage, latency arbitrage, HFT, tick scalping, etc.). Strategies must comply with the risk policy'],
        
            // Rule 8: Inactivity Period
            ['evaluation_rule_id' => 8, 'option_label' => '30 Days', 'option_value' => '30', 'is_getter' => false, 'description' => 'We will consider you are inactive and your account will be breached if you do not have any trading activity on your account for specified consecutive days'],
            ['evaluation_rule_id' => 8, 'option_label' => '60 Days', 'option_value' => '60', 'is_getter' => false, 'description' => 'We will consider you are inactive and your account will be breached if you do not have any trading activity on your account for specified consecutive days'],
        
            // Rule 9: Maximum Trading Days
            ['evaluation_rule_id' => 9, 'option_label' => 'Unlimited', 'option_value' => 'unlimited', 'is_getter' => false, 'description' => 'Maximum Time it`s allowed to trade on an Account'],
            ['evaluation_rule_id' => 9, 'option_label' => '30 Days', 'option_value' => '30', 'is_getter' => false, 'description' => 'Maximum Time it`s allowed to trade on an Account'],
        
            // Rule 10: IP Address/ VPN/ VPS
            ['evaluation_rule_id' => 10, 'option_label' => 'Allowed', 'option_value' => 'allowed', 'is_getter' => false, 'description' => 'VPN and VPS are allowed'],
            ['evaluation_rule_id' => 10, 'option_label' => 'Not Allowed', 'option_value' => 'not_allowed', 'is_getter' => false, 'description' => 'VPN and VPS are not allowed. It should be used only one IP'],
            ['evaluation_rule_id' => 10, 'option_label' => 'Allowed with Restrictions', 'option_value' => 'allowed_with_restrictions', 'is_getter' => true, 'description' => 'VPN and VPS are permitted. Traders must not use the same IP address with other users'],
        
            // Rule 11: Prohibited Trading Practices
            ['evaluation_rule_id' => 11, 'option_label' => 'Strictly Enforced', 'option_value' => 'strictly_enforced', 'is_getter' => true, 'description' => 'Practices like unfair advantages, exploiting price gaps, HFT bots, no risk management, are forbidden. Breaches result in account termination'],
            ['evaluation_rule_id' => 11, 'option_label' => 'Average Enforced', 'option_value' => 'average_enforced', 'is_getter' => true, 'description' => 'Practices like unfair advantages, exploiting price gaps, HFT bots, no risk management, are forbidden. Breaches result in account termination'],
            ['evaluation_rule_id' => 11, 'option_label' => 'Litely Enforced', 'option_value' => 'litely_enforced', 'is_getter' => true, 'description' => 'Practices like unfair advantages, exploiting price gaps, HFT bots, no risk management, are forbidden. Breaches result in account termination'],
            ['evaluation_rule_id' => 11, 'option_label' => 'Not Enforced', 'option_value' => 'not_enforced', 'is_getter' => false, 'description' => 'Practices like unfair advantages, exploiting price gaps, HFT bots, no risk management, are forbidden. Breaches result in account termination'],
        
            // Rule 12: Stop Loss Requirement
            ['evaluation_rule_id' => 12, 'option_label' => 'Yes', 'option_value' => 'yes', 'is_getter' => false, 'description' => 'Only for Funded Accounts'],
            ['evaluation_rule_id' => 12, 'option_label' => 'No', 'option_value' => 'no', 'is_getter' => false, 'description' => 'No stop loss requirement'],
        
            // Rule 13: Buyback
            ['evaluation_rule_id' => 13, 'option_label' => 'Yes', 'option_value' => 'yes', 'is_getter' => false, 'description' => 'You can restore a funded account instantly via Buyback by paying a fee, no challenge needed'],
            ['evaluation_rule_id' => 13, 'option_label' => 'No', 'option_value' => 'no', 'is_getter' => false, 'description' => 'Buyback option is not available'],
        
            // Rule 14: Scaling Plan
            ['evaluation_rule_id' => 14, 'option_label' => 'Yes', 'option_value' => 'yes', 'is_getter' => true, 'description' => 'In order to scale, the trader must profit 10% over the course of 4 months (2.5% per month). The trader is also required to process at least 1 payout per month. Afterwards, the trader\'s account will be rewarded with a 25% balance increase.'],
            ['evaluation_rule_id' => 14, 'option_label' => 'No', 'option_value' => 'no', 'is_getter' => false, 'description' => 'No scaling plan available'],
        
            // Rule 15: Risk Limits
            ['evaluation_rule_id' => 15, 'option_label' => 'Various', 'option_value' => 'various', 'is_getter' => true, 'description' => 'Floating profit and loss (PnL) must not exceed -1% of the account size. Violation is a Hard Breach'],
        
            // Rule 16: Other Rules
            ['evaluation_rule_id' => 16, 'option_label' => 'Various', 'option_value' => 'various', 'is_getter' => true, 'description' => 'Traders Cannot have a single trade exceed 30% of total profit during the challenge stage'],
        ];
        return $evaluationOptions;
    }

    public function getEvaluationRules()
    {
        $evaluationRules = [
            [
                'id' => 1,
                'label' => 'News Trading',
                'slug' => 'news-trading',
                'zone_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'label' => 'Copy Trading',
                'slug' => 'copy-trading',
                'zone_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'label' => 'Scalping',
                'slug' => 'scalping',
                'zone_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'label' => 'Hedging',
                'slug' => 'hedging',
                'zone_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'label' => 'Expert Advisors (EAs)',
                'slug' => 'expert-advisors-eas',
                'zone_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'label' => 'Position Over Weekend',
                'slug' => 'position-over-weekend',
                'zone_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'label' => 'All Trading Strategies',
                'slug' => 'all-trading-strategies',
                'zone_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'label' => 'Inactivity Period',
                'slug' => 'inactivity-period',
                'zone_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'label' => 'Maximum Trading Days',
                'slug' => 'maximum-trading-days',
                'zone_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'label' => 'IP Address/ VPN/ VPS',
                'slug' => 'ip-address-vpn-vps',
                'zone_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'label' => 'Prohibited Trading Practices',
                'slug' => 'prohibited-trading-practices',
                'zone_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 12,
                'label' => 'Stop Loss Requirement',
                'slug' => 'stop-loss-requirement',
                'zone_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 13,
                'label' => 'Buyback',
                'slug' => 'buyback',
                'zone_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 14,
                'label' => 'Scaling Plan',
                'slug' => 'scaling-plan',
                'zone_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 15,
                'label' => 'Risk Limits',
                'slug' => 'risk-limits',
                'zone_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 16,
                'label' => 'Other Rules',
                'slug' => 'other-rules',
                'zone_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        return $evaluationRules;
    }
}
