<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LoadDefaultUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:load-default-users';

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
        DB::statement("use fxrebate");
        $this->info("\\\\///...loading default users");
        $this->call('db:seed', ["class" => "\\Modules\\Auth\\Database\\Seeders\UsersSeeder"]);
    }
}
