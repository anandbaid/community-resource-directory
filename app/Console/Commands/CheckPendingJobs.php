<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CheckPendingJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for pending jobs in the database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = DB::table('jobs')->where('reserved_at', '=', null)->count();
        if ($count > 0) {
            Artisan::call('queue:work', [
                '--queue' => 'default',
                '--sleep' => 3,
                '--tries' => 3,
                '--stop-when-empty' => true,
            ]);
        }
    }
}
