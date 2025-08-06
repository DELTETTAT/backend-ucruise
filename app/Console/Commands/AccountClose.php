<?php

namespace App\Console\Commands;
use Illuminate\Http\Request;
use Illuminate\Console\Command;
use App\Http\Controllers\Api\CronJobs\CronjobsController;

class AccountClose extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run command to deactive account of resigned users if today last working day';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $controller = new CronjobsController();
        $controller->accountCloseDeactive(new Request());
    }
}
