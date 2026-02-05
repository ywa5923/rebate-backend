<?php

namespace Modules\Brokers\Database\Seeders;

use Modules\Brokers\Models\ChallengeCategory;
use Modules\Brokers\Models\ChallengeStep;
use Modules\Brokers\Models\ChallengeAmount;
use Illuminate\Database\Seeder;

class ChallengeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedChallengeCategories();
        $this->seedChallengeSteps();
        $this->seedChallengeAmounts();
    }

    public function seedChallengeCategories(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'Forex and CFDs','slug'=>'forex-and-cfds'],
            ['id' => 2, 'name' => 'Crypto','slug'=>'crypto'],
            ['id' => 3, 'name' => 'Instant Funding','slug'=>'instant-funding'],
        ];

        ChallengeCategory::insert($categories);
    }

    public function seedChallengeSteps(): void
    {
        $steps = [
            ['id' => 1, 'name' => '1 Step Evaluation','slug'=>'step-1','challenge_category_id'=>1],
            ['id' => 2, 'name' => '2 Step Evaluation','slug'=>'step-2','challenge_category_id'=>1],
            ['id' => 3, 'name' => '1 Step Evaluation','slug'=>'step-1','challenge_category_id'=>2],
            ['id' => 4, 'name' => '2 Step Evaluation','slug'=>'step-2','challenge_category_id'=>2],
            ['id' => 5, 'name'=>'No Step Evaluation','slug'=>'step-0','challenge_category_id'=>3],

        ];

        ChallengeStep::insert($steps);
    }

    public function seedChallengeAmounts(): void
    {
        $amounts = [
            ['id' => 1, 'amount' => '10', 'currency' => 'USD', 'challenge_category_id' => 1],
            ['id' => 2, 'amount' => '50', 'currency' => 'USD', 'challenge_category_id' => 1],
            ['id' => 3, 'amount' => '100', 'currency' => 'USD', 'challenge_category_id' => 1],
            ['id' => 4, 'amount' => '150', 'currency' => 'USD', 'challenge_category_id' => 1],
            ['id' => 5, 'amount' => '200', 'currency' => 'USD', 'challenge_category_id' => 1],
            ['id' => 6, 'amount' => '250', 'currency' => 'USD', 'challenge_category_id' => 1],
            ['id' => 7, 'amount' => '10', 'currency' => 'USD', 'challenge_category_id' => 2],
            ['id' => 8, 'amount' => '100', 'currency' => 'USD', 'challenge_category_id' => 2],
            ['id' => 9, 'amount' => '200', 'currency' => 'USD', 'challenge_category_id' => 2],
            ['id' => 10, 'amount' => '300', 'currency' => 'USD', 'challenge_category_id' => 2],
            ['id' => 11, 'amount' => '400', 'currency' => 'USD', 'challenge_category_id' => 2],
            ['id' => 12, 'amount' => '500', 'currency' => 'USD', 'challenge_category_id' => 2],
            ['id' => 13, 'amount' => '10', 'currency' => 'USD', 'challenge_category_id' => 3],
            ['id' => 14, 'amount' => '50', 'currency' => 'USD', 'challenge_category_id' => 3],
            ['id' => 15, 'amount' => '100', 'currency' => 'USD', 'challenge_category_id' => 3],
            ['id' => 16, 'amount' => '150', 'currency' => 'USD', 'challenge_category_id' => 3],
            ['id' => 17, 'amount' => '200', 'currency' => 'USD', 'challenge_category_id' => 3],
            ['id' => 18, 'amount' => '250', 'currency' => 'USD', 'challenge_category_id' => 3],
        ];
        ChallengeAmount::insert($amounts);
    }
}
