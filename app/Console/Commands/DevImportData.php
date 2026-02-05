<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Brokers\Services\ChallengeService;
use Illuminate\Support\Facades\DB;
class DevImportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:dev-import-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $this->cloneDefaultChallengesToBroker(181);
    }

    public function cloneDefaultChallengesToBroker(int $brokerId): bool
    {
        //first flush data
        $challengeCategories = DB::table('challenge_categories')->where('broker_id', $brokerId)->get();
        foreach ($challengeCategories as $challengeCategory) {
            DB::table('challenge_steps')->where('challenge_category_id', $challengeCategory->id)->delete();
            DB::table('challenge_amounts')->where('challenge_category_id', $challengeCategory->id)->delete();
        }
        $challengeService = app(ChallengeService::class);
        $challengeService->cloneDefaultChallengesToBroker($brokerId);
        $this->info("Default challenges cloned to broker $brokerId");
        return true;
    }
}
