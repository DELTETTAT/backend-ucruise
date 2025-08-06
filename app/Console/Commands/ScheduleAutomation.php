<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Http\Controllers\Api\RouteAutomation\RouteAutomationController;


class ScheduleAutomation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:automation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run command to genearte schedule every day dynamically';

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle()
    {
        //$selectedDbs = ['uc_nikh','uc_sdna','uc_tech'];

        $controller = new RouteAutomationController();
        $controller->dailyAutomation();
        
        // foreach($selectedDbs as $db){
        //    $controller->dailyAutomation($db);
        // }
        
    }
}
