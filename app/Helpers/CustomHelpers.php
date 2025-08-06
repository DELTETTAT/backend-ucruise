<?php 
use App\Models\Schedule;
use App\Models\ScheduleCarer;
use App\Models\ScheduleClient;
use App\Models\ScheduleMileageClient;
use App\Models\ScheduleTask;

if (!function_exists('scheduleData')) {
    function scheduleData($datesInRange,$id)
    {
         $data = [];
        
            foreach ($datesInRange as $date) {
                $user_ids = 13;
                $schedules = Schedule::where(function ($query)  use ($date) {

                    $query->whereDate("date", $date);
                    $query->exists();
       
                })->orwhere(function ($query) {
       
                    $query->where('is_repeat', 1);
       
                  $query->where('end_date', '>', now());
       
                })->with(['clients' => function($q) use($user_ids) {
       
        
       
                    $q->where('client_id', $user_ids);
       
        
       
                   // $q->with('user');
       
                    
       
                }])->first();


                $data[$date] = $schedules;
                // if($scheduleData->is_repeat  == 1){
                //     $data['2023-10-02'] = $scheduleData;
                // }

                
            }
        //return json_encode($data); die;
            echo '<pre>';print_r($data);

    }
}

?>