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
            ['name' => '1 Step Evaluation','slug'=>'1-step-evaluation','challenge_category_id'=>1],
            ['name' => '2 Step Evaluation','slug'=>'2-step-evaluation','challenge_category_id'=>1],
            ['name' => '1 Step Evaluation','slug'=>'1-step-evaluation','challenge_category_id'=>2],
            ['name' => '2 Step Evaluation','slug'=>'2-step-evaluation','challenge_category_id'=>2],
            ['name'=>'0 Step Evaluation','slug'=>'0-step-evaluation','challenge_category_id'=>3],

        ];

        ChallengeStep::insert($steps);
    }

    public function seedChallengeAmounts(): void
    {
        $amounts = [
            ['amount' => '10', 'currency' => 'USD', 'challenge_category_id' => 1],
            ['amount' => '50', 'currency' => 'USD', 'challenge_category_id' => 1],
            ['amount' => '100', 'currency' => 'USD', 'challenge_category_id' => 1],
            ['amount' => '150', 'currency' => 'USD', 'challenge_category_id' => 1],
            ['amount' => '200', 'currency' => 'USD', 'challenge_category_id' => 1],
            ['amount' => '250', 'currency' => 'USD', 'challenge_category_id' => 1],
            ['amount' => '10', 'currency' => 'USD', 'challenge_category_id' => 2],
            ['amount' => '100', 'currency' => 'USD', 'challenge_category_id' => 2],
            ['amount' => '200', 'currency' => 'USD', 'challenge_category_id' => 2],
            ['amount' => '300', 'currency' => 'USD', 'challenge_category_id' => 2],
            ['amount' => '400', 'currency' => 'USD', 'challenge_category_id' => 2],
            ['amount' => '500', 'currency' => 'USD', 'challenge_category_id' => 2],
            ['amount' => '10', 'currency' => 'USD', 'challenge_category_id' => 3],
            ['amount' => '50', 'currency' => 'USD', 'challenge_category_id' => 3],
            ['amount' => '100', 'currency' => 'USD', 'challenge_category_id' => 3],
            ['amount' => '150', 'currency' => 'USD', 'challenge_category_id' => 3],
            ['amount' => '200', 'currency' => 'USD', 'challenge_category_id' => 3],
            ['amount' => '250', 'currency' => 'USD', 'challenge_category_id' => 3],
        ];
        ChallengeAmount::insert($amounts);
    }
}
