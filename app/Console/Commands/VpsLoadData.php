<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VpsLoadData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:vps-load-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load data for VPS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $this->call('app:load-default-data');
         $this->call('app:load-default-users');
         $this->call('app:load-locales');
         
    }
}
