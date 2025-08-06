<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $superAdminRole = Role::firstOrCreate(['name'=>'superadmin']);
        $admin = Role::firstOrCreate(['name'=>'admin']);
        $user = Role::firstOrCreate(['name'=>'staff']);
        $client = Role::firstOrCreate(['name'=>'client']);
        $driver=Role::firstorCreate(['name'=>'driver']);
        $archived_user = Role::firstOrCreate(['name'=>'archived_user']);

        $superAdmin = User::firstOrCreate(['email'=>'admin@admin.com']);
        $superAdmin->password = bcrypt('admin@123');
        $superAdmin->first_name = 'Super Admin';
        if($superAdmin->save()){
            if(!$superAdmin->hasRole('superadmin')){
                $superAdmin->roles()->attach($superAdminRole);
            }
        }


        \DB::table('time_attendences')->insert(array(
            array(
            'notice_preiod' => 3,
            'attendance_threshold' => 10,
            'timesheet_precision' => '1 decimal',
            'pay_rate' => 'End Time',
            'pay_rate' => 'End Time',
            'clockin_alert_message' => 'If you are feeling unwell or have any covid symptoms please contact your supervisor.',
            ),
            ));


            \DB::table('timezones')->insert(array(
                array(
                'timezone' => "International Date Line West"
                ),
                array(
                    'timezone' => "American Samoa"
                    ),
                array(
                    'timezone' => "Midway Island"
                    ),
                array(
                        'timezone' => "Hawaii"
                        ),
                array(
                        'timezone' => "Alaska"
                        ),
                 array(
                        'timezone' => "Pacific Time (US & Canada)"
                        ),
                array('timezone' => "Tijuana"),
                array('timezone' => "Arizona"),
                array('timezone' => "Mazatlan"),
                array('timezone' => "Mountain Time (US & Canada)"),
                array('timezone' => "Central Time (US & Canada)"),

                ));

                $this->call(QuizlevelSeeder::class);
                $this->call(QuestiontypeSeeder::class);
                $this->call(ReasonTypeSeeder::class);
                $this->call(HrmsPermissionSeeder::class);
                $this->call(HrmsNewRoleSeeder::class);
                $this->call(PayrollScheduleSettingSeeder::class);

    }
}
