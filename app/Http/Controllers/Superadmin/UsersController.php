<?php

namespace App\Http\Controllers\Superadmin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{DB, Auth, Artisan, Session, Validator, Config, File, Hash, Mail, Storage};
use App\Models\{Role, User, Notification, ReportHeadingCategory, Notes, Scheduler, TimeAttendence, Note_permission, BulkEmail, Teams, BulkSms, CompanyAddresse, CompanyDetails, Language, StaffSettings, StaffPayrollSettings, StaffKin, StaffNote, StaffDocument, Module, Permission, ScheduleTemplate, SubUser, GroupLoginUser, Image};
use Illuminate\Support\Facades\Schema;

error_reporting();
class UsersController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function managePermissions()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::get();

        return view("demo")->with(["roles" => $roles, "permissions" => $permissions]);
    }

    public function storePermissions(Request $request)
    {

        foreach ($request->all() as $key => $req) {
            $output = explode("_", $key);
            if ($output[0] == "permissions") {
                $role = Role::find($output[1]);
                $role->permissions()->sync($req);
            }
        }

        return redirect()->route('managePermissions');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $this->data["admins"] = User::whereHas("roles", function ($q) {
                $q->where("name", "admin");
            })
                ->where('close_account', 1)
                ->with('userSubscription.subscription')
                ->orderBy("id", "DESC")
                ->get();
            return view("superadmin.admin.index", $this->data);
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view("superadmin.admin.add");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->merge([
            'company_name' => str_replace(' ', '', strtolower($request->company_name)),
        ]);

        $validator = Validator::make($request->all(), [
            "email" => "required|email|unique:users,email",
            "phone" => "required|unique:users,phone",
            "company_name" => "required|unique:users,company_name",
        ]);


        if ($validator->fails()) {

            $errors = $validator->errors();
            Session::flash("error", $errors);
            return redirect()->back();
        }

        $store = new User();
        $store->first_name = $request->first_name;
        $store->last_name = $request->last_name;
        $store->email = $request->email;
        $store->phone = $request->phone;
        $store->status = $request->status;
        $store->company_name = $request->company_name;
        $admin = Role::where("name", "admin")->first();

        $entity = 1;
        if ($entity == 1) {
            // try {
            $schemaName =
                "UC_" . $request->company_name ?:
                config("database.connections.mysql.database");

            // Update database name
            $rand = Str::random(10);
            $hashed_random_password = Hash::make($rand);

            $store->database_path = env("DB_HOST");
            $store->database_name = $schemaName;
            $store->database_username = env("DB_USERNAME");
            $store->database_password = env("DB_PASSWORD");
            $store->password = $hashed_random_password;
            $store->save();

            // Add Role
            if (!$store->hasRole("admin")) {
                $store->roles()->attach($admin);
            }

            // config(["database.connections.mysql.database" => null]);

            $query = "CREATE DATABASE $schemaName;";

            DB::statement($query);

            //connecting Child DB
            $this->connectDB($schemaName);
            // \Config::set("database.connections.$schemaName", $default);

            // config(["database.connections.mysql.database" => $schemaName]);


            Artisan::call("migrate", [
                "--database" => $schemaName,
                "--force" => true,
            ]);
            Artisan::call("db:seed", [
                "--class" => "AdminRole",
                "--database" => $schemaName,
            ]);

            Artisan::call("db:seed", [
                "--class" => "Timeattendence",
                "--database" => $schemaName,
            ]);

            Artisan::call("db:seed", [
                "--class" => "TimeZone",
                "--database" => $schemaName,
            ]);

            Artisan::call("db:seed", [
                "--class" => "Language",
                "--database" => $schemaName,
            ]);

            Artisan::call("db:seed", [
                "--class" => "PriceBook",
                "--database" => $schemaName,
            ]);

            Artisan::call("db:seed", [
                "--class" => "ClientType",
                "--database" => $schemaName,
            ]);
            Artisan::call("db:seed", [
                "--class" => "Xeropay",
                "--database" => $schemaName,
            ]);
            Artisan::call("db:seed", [
                "--class" => "PermissionSeeder",
                "--database" => $schemaName,
            ]);

            Artisan::call("db:seed", [
                "--class" => "Report_heading",
                "--database" => $schemaName,
            ]);
            Artisan::call("db:seed", [
                "--class" => "ShiftTypeSeeder",
                "--database" => $schemaName,
            ]);

            Artisan::call("db:seed", [
                "--class" => "StatusSeeder",
                "--database" => $schemaName,
            ]);

            Artisan::call("db:seed", [
                "--class" => "AddDefaultPrice",
                "--database" => $schemaName,
            ]);

            Artisan::call("db:seed", [
                "--class" => "QuizlevelSeeder",
                "--database" => $schemaName,
            ]);

            Artisan::call("db:seed", [
                "--class" => "QuestiontypeSeeder",
                "--database" => $schemaName,
            ]);

            Artisan::call("db:seed", [
                "--class" => "ReasonTypeSeeder",
                "--database" => $schemaName,
            ]);

            Artisan::call("db:seed", [
                "--class" => "HrmsPermissionSeeder",
                "--database" => $schemaName,
            ]);

            Artisan::call("db:seed", [
                "--class" => "HrmsNewRoleSeeder",
                "--database" => $schemaName,
            ]);

            Artisan::call("db:seed", [
                "--class" => "PayrollScheduleSettingSeeder",
                "--database" => $schemaName,
            ]);


            //creating the sub-admin in child DB
            $child_store = new User();
            $child_store->id = $store->id;
            $child_store->first_name = $request->first_name;
            $child_store->last_name = $request->last_name;
            $child_store->email = $request->email;
            $child_store->phone = $request->phone;
            $child_store->status = $request->status;
            $child_store->company_name = $request->company_name;
            $admin = Role::where("name", "admin")->first();

            $child_store->database_path = env("DB_HOST");
            $child_store->database_name = $schemaName;
            $child_store->database_username = env("DB_USERNAME");
            $child_store->database_password = env("DB_PASSWORD");
            $child_store->password = $hashed_random_password;
            $child_store->save();

            // Add Role
            if (!$child_store->hasRole("admin")) {
                $child_store->roles()->attach($admin);
            }
            if ($child_store->hasRole("admin")) {
                DB::table('company_details')->updateOrInsert(['id' => 1], ['name' => $request->company_name, 'email' => $request->email, 'phone' => $request->phone]);
                DB::table('ride_settings')->updateOrInsert(['id' => 1], ['female_safety' => 1, 'noshow_frequency' => 'monthly', 'noshow_count' => 5, 'noshow' => 1,  'noshow_timer' => '00:05:00', 'leave_timer' => '00:05:00']);
            }

            // \DB::table($schemaName.'users')->insert([
            //     'first_name' => 'Niya Sethi',
            //     'email' => 'niya@gmail.com'
            //      ]);

            DB::disconnect($schemaName);

            // Send email to admin for login details

            $this->data["detais"] = [
                "first_name" => $request->first_name . ' ' . $request->last_name,
                "email" => $request->email,
                "pass" => $rand,
            ];
            $email = $request->email;
            //return view('email.adminlogin',$this->data);
            // dd($data);
            Mail::send("email.adminlogin", $this->data, function (
                $message
            ) use ($email) {
                $message
                    ->to($email)
                    ->from("unifygroup@unifytechsolutions.com")
                    ->subject("Admin Login Details");
            });
            // } catch (Exception $ex) {
            //     Log::info($ex->getMessage());
            //     return $ex->getMessage();
            // }
        }

        Session::flash("message", "You have successfully added.");
        return redirect()->route("admin.index");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $this->data["edit"] = User::where("id", $id)->first();
            return view("superadmin.admin.edit", $this->data);
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                "email" => "required|email|unique:users,email, " . $id,
                "phone" => "required|unique:users,phone, " . $id,
            ]);

            $update = User::where("id", $id)->first();
            $update->first_name = $request->first_name;
            $update->last_name = $request->last_name;
            $update->email = $request->email;
            $update->phone = $request->phone;
            $update->status = $request->status;
            //$store->company_name = $request->company_name;
            //$admin = Role::where('name','admin')->first();
            if ($update->save()) {
                // if(!$store->hasRole('admin')){
                //     $store->roles()->attach($admin);
                // }
            }

            Session::flash("message", "You have successfully updated.");
            return redirect()->route("admin.index");
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function destroy($id)
    // {
    //     try {
    //         $userData = User::where("id", $id)->first();
    //         // dd($userData->database_name);

    //         $config = config('database.connections.mysql');
    //         unset($config['options']['--force']);

    //         $databaseName = $userData->database_name;
    //         //DB::connection($config)->getPdo();
    //         DB::statement("DROP DATABASE IF EXISTS `$databaseName`");

    //         $userData->delete();

    //         Session::flash("warning", "You have successfully deleted.");
    //         return redirect()->route("admin.index");
    //     } catch (\Exception $ex) {
    //         $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
    //         Session::flash("error", $this->data["msg"]);
    //         return redirect()->back();
    //     }
    // }




    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Get the main user data
            $userData = User::findOrFail($id);
            $databaseName = $userData->database_name;

            // 1. First delete role assignments for subusers
            if (Schema::hasTable('role_sub_user')) {
                DB::table('role_sub_user')
                    ->whereIn('sub_user_id', function ($query) use ($databaseName) {
                        $query->select('id')
                            ->from('sub_users')
                            ->where('database_name', $databaseName);
                    })
                    ->delete();
            }

            // 2. Then delete role assignments for main users
            if (Schema::hasTable('role_user')) {
                DB::table('role_user')
                    ->whereIn('user_id', function ($query) use ($databaseName) {
                        $query->select('id')
                            ->from('users')
                            ->where('database_name', $databaseName);
                    })
                    ->delete();
            }

            // 3. Delete the subusers
            if (Schema::hasTable('sub_users')) {
                DB::table('sub_users')
                    ->where('database_name', $databaseName)
                    ->delete();
            }

            // 4. Delete other users sharing this database (except main user)
            User::where('database_name', $databaseName)
                ->where('id', '!=', $id)
                ->delete();

            // 5. Drop the database
            DB::statement("DROP DATABASE IF EXISTS `$databaseName`");

            // 6. Finally delete the main user
            $userData->forcedelete();

            DB::commit();

            Session::flash("warning", "Successfully deleted user, all subusers, roles, and associated database.");
            return redirect()->route("admin.index");
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }


    // Only empty database as added users

    public function emptyDatabase($id){

        try {
             DB::beginTransaction();

            // Get the main user data
            $userData = User::findOrFail($id);
            $databaseName = $userData->database_name;
            //$databaseName = strtolower($databaseName);


            // Connect child database 
            $this->connectDB($databaseName);

            if (Schema::hasTable('role_sub_user')) {
                DB::table('role_sub_user')
                    ->whereIn('sub_user_id', function ($query) use ($databaseName) {
                        $query->select('id')
                            ->from('sub_users')
                            ->where('database_name', $databaseName);
                    })
                    ->delete();
            }

            // 2. Then delete role assignments for main users
           if (Schema::hasTable('role_user')) {
                DB::table('role_user')
                    ->whereIn('user_id', function ($query) use ($databaseName, $id) {
                        $query->select('id')
                            ->from('users')
                            ->where('database_name', $databaseName)
                            ->where('id', '!=', $id);
                    })
                    ->delete();
            }

            DB::table('employee_team_managers')->delete();
            DB::table('employee_separations')->delete();
            DB::table('sub_user_addresses')->delete();
            DB::table('hrms_time_and_shifts')->delete();
            DB::table('team_managers')->delete();
            DB::table('hrms_teams')->delete();
            DB::table('user_infos')->delete();
            DB::table('import_employees_salary_from_excels')->delete();
            DB::table('hrms_employee_roles')->delete();
            DB::table('employees_under_of_managers')->delete();
            DB::table('sub_users')->delete();

            // 4. Delete other users sharing this database (except main user)
           DB::table('users')->where('id','!=',$id)->delete();


            $defaultDb = env('DB_DATABASE');
            $this->connectDB($defaultDb); 

            $databaseNameparent = DB::connection()->getDatabaseName();
            // Get sub_user IDs from parent
             $subUserIds = DB::table('sub_users')
                ->where('database_name', $databaseName)
                ->pluck('id');

            // Delete their roles and accounts
            foreach ($subUserIds as $subId) {
                DB::table('role_sub_user')->where('sub_user_id', $subId)->delete();
                DB::table('sub_users')->where('id', $subId)->delete();
            }

            DB::commit();
            Session::flash("warning", "Successfully empty database");
            return redirect()->route("admin.index");

        } catch (\Throwable $ex) {

             $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }


    // Delete all drivers

    public function deleteAllDrivers($id){

          try {

             DB::beginTransaction();

            // Get the main user data
            $userData = User::findOrFail($id);
            $temp_DB_name = $userData->database_name;
            //$databaseName = strtolower($databaseName);

            // Connect child database 
            $this->connectDB($temp_DB_name);
            $default_DBName = env("DB_DATABASE"); 
            
            // Get all sub_users with role "driver" or "archived_driver"
            $drivers = SubUser::whereHas("roles", function ($q) {
                $q->whereIn("name", ["driver", "archived_driver"]);
            })->get();

            foreach ($drivers as $sub_user) {
                // Switch to default DB
                $this->connectDB($default_DBName);
                DB::table('role_sub_user')->where('sub_user_id', $sub_user->id)->delete();
                SubUser::where('id', $sub_user->id)->forceDelete();

                // Switch back to original (tenant) DB
                $this->connectDB($temp_DB_name);
                DB::table('role_sub_user')->where('sub_user_id', $sub_user->id)->delete();
                $sub_user->forceDelete();
            }

            DB::commit();
            Session::flash("warning", "Successfully deleted drivers");
            return redirect()->route("admin.index");

        } catch (\Throwable $ex) {

             $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }

    }



    // Send bulk Announcement
    public function sendAnnouncement(Request $request)
    {
        try {
            $this->data["allUsers"] = User::get();
            return view("superadmin.announcement.announcement", $this->data);
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }
    // store bulk Announcement
    public function storeAnnouncement(Request $request)
    {
        try {
            //echo \Storage::url('announcment');die;
            $allUsers = User::whereHas("roles", function ($q) {
                $q->where("name", "admin");
            })->get();

            foreach ($allUsers as $user) {
                $not = new Notification();
                $not->sender_id = Auth::Id();
                $not->receiver_id = $user->id;
                $not->title = $request->title;

                if ($request->file) {
                    $image = $request->file("file");
                    $filename = str_replace(
                        " ",
                        "",
                        md5(time()) . "_" . $image->getClientOriginalName()
                    );
                    $FileEnconded = File::get($request->file);
                    $path = "announcment/" . $filename;
                    Storage::put($path, (string) $FileEnconded, "public");
                    $not->image = $filename;
                }

                // $not->image = $request->title;
                $not->message = $request->description;
                $not->read_status = 0;
                $not->save();
            }

            Session::flash(
                "success",
                "You have successfully send announcement."
            );
            return redirect("/admin/send-announcement");
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }
    public function scheduler()
    {
        return view("superadmin.scheduler.index");
    }

    // list notification
    public function listAnnouncement()
    {
        try {
            $this->data["announces"] = Notification::orderBy(
                "id",
                "DESC"
            )->groupby('title')->get();
            return view("superadmin.announcement.index", $this->data);
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    // deleteAnnouncement
    public function deleteAnnouncement($id)
    {
        try {
            Notification::where("id", $id)->delete();
            Session::flash("warning", "You have successfully deleted.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    // Seen notification
    public function notificationSeen($id)
    {
        try {
            $not = Notification::where("id", $id)->first();
            $not->read_status = 1;
            $not->save();
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    // My Account
    public function myAccount()
    {
        try {
            $this->data["user"] = User::where("id", Auth::Id())->first();
            return view("superadmin.profile", $this->data);
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $update = User::where("id", Auth::Id())->first();
            $update->first_name = $request->first_name;
            $update->last_name = $request->last_name;

            $update->phone = $request->phone;
            $update->save();
            Session::flash("success", "You have successfully updated.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    // delete category

    public function deleteCategory($id, $tableName)
    {
        try {
            DB::table($tableName)->where('id', $id)->delete();
            Session::flash("warning", "You have successfully deleted.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }


    // Progress Note
    public function progressList()
    {
        Session::put('cat', \Request::route()->getName());
        try {
            $this->data["notes"] = Notes::where('category_name', \Request::route()->getName())->orderBy("id", "DESC")
                ->get();
            return view("note.index", $this->data);
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    public function addNotes()
    {
        //echo \Session::get('cat');
        return view("note.add");
    }

    public function noteStore(Request $request)
    {
        //echo \Session::get('cat');
        try {
            $note = new Notes();
            $note->heading = $request->heading;
            $note->mandatory = $request->mandatory ? 1 : 0;
            $note->category_name = $request->category_name;
            $note->save();

            Session::flash("message", "You have successfully added.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }


    public function editNote($id)
    {
        $this->data["edit"] = Notes::where('id', $id)->first();
        return view("note.edit", $this->data);
    }


    public function noteUpdate($id, Request $request)
    {
        //echo \Session::get('cat');
        try {
            $note = Notes::where('id', $id)->first();
            $note->heading = $request->heading;
            $note->mandatory = $request->mandatory;
            $note->category_name = Session::get('cat');
            $note->save();

            Session::flash("message", "You have successfully updated.");
            return redirect()->route(Session::get('cat'));
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    // For client types store data
    // Upload Qualification categoty
    public function clientTypeStore(Request $request)
    {
        try {
            $doc = new Scheduler();
            $doc->name = $request->name;
            $doc->save();

            Session::flash("success", "You have successfully added.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }




    // Update settings
    public function updateSettings(Request $request)
    {


        try {
            if ($request->timezone) {
                DB::table('settings')->updateOrInsert(['id' => 1], ['timezone' => $request->timezone]);
            }

            if ($request->minute_interval) {
                DB::table('settings')->updateOrInsert(['id' => 1], ['minute_interval' => $request->minute_interval]);
            }

            if ($request->pay_run) {
                DB::table('settings')->updateOrInsert(['id' => 1], ['pay_run' => $request->pay_run]);
            }

            if ($request->manage_shift) {
                DB::table('settings')->updateOrInsert(['id' => 1], ['manage_shift' => $request->manage_shift]);
            }

            if ($request->first_day_fortnight) {
                DB::table('settings')->updateOrInsert(['id' => 1], ['first_day_fortnight' => $request->first_day_fortnight]);
            }

            Session::flash("success", "You have successfully updated.");
            return redirect('users/account#' . $request->redirect);
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    // Update settings
    public function clientUpdateSettings(Request $request)
    {

        try {
            if ($request->timezone) {
                DB::table('settings')->updateOrInsert(['id' => 1], ['timezone' => $request->timezone]);
            }

            if ($request->minute_interval) {
                DB::table('settings')->updateOrInsert(['id' => 1], ['minute_interval' => $request->minute_interval]);
            }

            if ($request->pay_run) {
                DB::table('settings')->updateOrInsert(['id' => 1], ['pay_run' => $request->pay_run]);
            }

            if ($request->can_manage_shifts) {
                DB::table('settings')->updateOrInsert(['id' => 1], ['manage_shift' => 1]);
            } else {
                DB::table('settings')->updateOrInsert(['id' => 1], ['manage_shift' => 0]);
            }

            if ($request->first_day_fornight) {
                DB::table('settings')->updateOrInsert(['id' => 1], ['first_day_fortnight' => $request->first_day_fornight]);
            }


            Session::flash("success", "You have successfully updated.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }


    // Update time attendence
    public function updateTimeAttendence(Request $request)
    {

        if ($request->enable_unavailability) {
            DB::table('time_attendences')->updateOrInsert(['id' => 1], ['enable_unavailability' => $request->enable_unavailability]);
        } else {
            DB::table('time_attendences')->updateOrInsert(['id' => 1], ['enable_unavailability' => 'off']);
        }

        if ($request->location_check) {
            DB::table('time_attendences')->updateOrInsert(['id' => 1], ['location_check' => $request->location_check]);
        } else {
            DB::table('time_attendences')->updateOrInsert(['id' => 1], ['location_check' => 'off']);
        }

        if ($request->auto_approve_shift) {
            DB::table('time_attendences')->updateOrInsert(['id' => 1], ['auto_approve_shift' => $request->auto_approve_shift]);
        } else {
            DB::table('time_attendences')->updateOrInsert(['id' => 1], ['auto_approve_shift' => 'off']);
        }

        if ($request->clockin_alert) {
            DB::table('time_attendences')->updateOrInsert(['id' => 1], ['clockin_alert' => $request->clockin_alert]);
        } else {
            DB::table('time_attendences')->updateOrInsert(['id' => 1], ['clockin_alert' => 'off']);
        }

        $attendance = TimeAttendence::find(1);
        if (!$attendance) {
            $attendance = new TimeAttendence();
            $attendance->id = 1;
        }
        $attendance->notice_preiod = $request->notice_period;
        $attendance->attendance_threshold = $request->attendance_threshold;
        $attendance->timesheet_precision = $request->time_precision;
        $attendance->pay_rate = $request->pay_rate;
        $attendance->clockin_alert_message = $request->clock_alert_message;
        $attendance->save();

        Session::flash("success", "You have successfully updated.");
        return redirect()->route("account");
    }
    public function updateRideSetting(Request $request)
    {

        if ($request->female_employee_security) {
            DB::table('ride_settings')->updateOrInsert(['id' => 1], ['female_safety' => $request->female_employee_security]);
        } else {
            DB::table('ride_settings')->updateOrInsert(['id' => 1], ['female_safety' => 0]);
        }
        if ($request->noshow_frequency) {
            DB::table('ride_settings')->updateOrInsert(['id' => 1], ['noshow_frequency' => $request->noshow_frequency]);
        } else {
            DB::table('ride_settings')->updateOrInsert(['id' => 1], ['noshow_frequency' => 'monthly']);
        }
        if ($request->noshow_count) {
            DB::table('ride_settings')->updateOrInsert(['id' => 1], ['noshow_count' => $request->noshow_count]);
        } else {
            DB::table('ride_settings')->updateOrInsert(['id' => 1], ['noshow_count' => 'monthly']);
        }
        if ($request->show_noshow_timer) {
            DB::table('ride_settings')->updateOrInsert(['id' => 1], ['noshow' => $request->show_noshow_timer]);
        } else {
            DB::table('ride_settings')->updateOrInsert(['id' => 1], ['noshow' => 1]);
        }
        if ($request->leave_timer) {
            DB::table('ride_settings')->updateOrInsert(['id' => 1], ['leave_timer' => $request->leave_timer]);
        } else {
            DB::table('ride_settings')->updateOrInsert(['id' => 1], ['leave_timer' => "00:05"]);
        }
        if ($request->show_noshow_timer == 1) {
            if ($request->noshow_timer) {
                DB::table('ride_settings')->updateOrInsert(['id' => 1], ['noshow_timer' => $request->noshow_timer]);
            } else {
                DB::table('ride_settings')->updateOrInsert(['id' => 1], ['noshow_timer' => "00:05"]);
            }
        } else {
            DB::table('ride_settings')->updateOrInsert(['id' => 1], ['noshow_timer' => "00:00"]);
        }
        Session::flash("success", "You have successfully updated.");
        return redirect()->route("account");
    }

    // Update Note Permission
    public function notePermission(Request $request)
    {
        if ($request->note_edit) {
            Note_permission::updateOrInsert(['id' => 1], ['note_edit' => $request->note_edit]);
        } else {
            Note_permission::updateOrInsert(['id' => 1], ['note_edit' => 'off']);
        }
        if ($request->expire_access) {
            Note_permission::updateOrInsert(['id' => 1], ['expire_access' => $request->expire_access]);
        }

        Session::flash("success", "You have successfully updated.");
        return redirect()->route("account");
    }

    // Update company name
    public function updateCompany(Request $request)
    {

        if ($request->name) {
            DB::table('company_details')->updateOrInsert(['id' => 1], ['name' => $request->name]);
        }
        if ($request->phone) {
            DB::table('company_details')->updateOrInsert(['id' => 1], ['phone' => $request->phone]);
        }
        if ($request->country) {
            DB::table('company_details')->updateOrInsert(['id' => 1], ['country' => $request->country]);
        }

        if ($request->file) {

            $image = $request->file("file");
            // $filename = str_replace(
            //     " ",
            //     "",
            //     md5(time()) . "_" . $image->getClientOriginalName()
            // );
            // $FileEnconded = \File::get($request->file);
            // $path = "logo/" . $filename;
            // \Storage::put($path, (string) $FileEnconded, "public");
            // \DB::table('company_details')->updateOrInsert(['id' => 1],['logo' => $filename]);

            $path = public_path('images/');
            !is_dir($path) &&
                mkdir($path, 0777, true);


            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move($path, $filename);
            DB::table('company_details')->updateOrInsert(['id' => 1], ['logo' => $filename]);
        }

        Session::flash("success", "You have successfully updated.");
        return redirect()->route("account");
    }

    public function companyMap()
    {
        $company = CompanyDetails::first();
        $companyDetails = DB::table('company_addresses')
            ->where('company_addresses.company_id', $company->id)
            ->whereNull('company_addresses.end_date')
            ->join('company_details', 'company_addresses.company_id', '=', 'company_details.id')
            ->select('company_details.id', 'company_details.name', 'company_details.logo', 'company_details.email', 'company_details.phone', 'company_addresses.address', 'company_addresses.latitude', 'company_addresses.longitude')
            ->first();

        $this->data['company_details'] = $companyDetails ? $companyDetails : $company;

        return view('staff.account.map', $this->data);
    }

    public function updateCompanyLoction(Request $request)
    {
        //dd($request->all());
        $company = CompanyDetails::find(1);

        if ($company) {
            $company_address = CompanyAddresse::whereNull('end_date')->first();

            if ($company_address) {
                if ($company_address->start_date == date('Y-m-d')) {

                    $company_address->company_id = $company->id;
                    $company_address->address = $request->address;
                    $company_address->latitude = $request->latitude;
                    $company_address->longitude = $request->longitude;
                } else {
                    $company_address->end_date = date('Y-m-d');
                    $company_details = new CompanyAddresse();

                    $company_details->company_id = $company->id;
                    $company_details->address = $request->address;
                    $company_details->latitude = $request->latitude;
                    $company_details->longitude = $request->longitude;
                    $company_details->start_date = date('Y-m-d');
                    $company_details->save();
                }
                $company_address->update();
            } else {

                $company_details = new CompanyAddresse();

                $company_details->company_id = $company->id;
                $company_details->address = $request->address;
                $company_details->latitude = $request->latitude;
                $company_details->longitude = $request->longitude;
                $company_details->start_date = date('Y-m-d');

                $company_details->save();
            }
        }

        return response()->json(['success' => true, "message" => "Updated successfully"], 200);
    }
    public function storeTemplate(Request $request)
    {
        //dd($request->all());
        $week_arr = array();
        $schedule_template = new ScheduleTemplate();
        $schedule_template->pick_time = $request->pick_time;
        $schedule_template->drop_time = $request->drop_time;
        $schedule_template->title = $request->title;
        $schedule_template->shift_finishes_next_day = $request->shift_finishes_next_day ? 1 : 0;
        $schedule_template->pricebook_id = $request->pricebook;
        $schedule_template->is_repeat = $request->is_repeat ? 1 : 0;
        if ($request->is_repeat) {
            if ($request->reacurrance == "daily") {
                $schedule_template->reacurrance = 0;
                $schedule_template->repeat_time = $request->repeat_days;
            } else if ($request->reacurrance == "weekly") {
                $schedule_template->reacurrance = 1;
                $schedule_template->repeat_time = $request->repeat_weeks;
                if ($request->mon) {
                    array_push($week_arr, "mon");
                }
                if ($request->tue) {
                    array_push($week_arr, "tue");
                }
                if ($request->wed) {
                    array_push($week_arr, "wed");
                }
                if ($request->thu) {
                    array_push($week_arr, "thu");
                }
                if ($request->fri) {
                    array_push($week_arr, "fri");
                }
                if ($request->sat) {
                    array_push($week_arr, "sat");
                }
                if ($request->sun) {
                    array_push($week_arr, "sun");
                }
                $schedule_template->occurs_on = json_encode($week_arr);
            } else if ($request->reacurrance == "monthly") {
                $schedule_template->reacurrance = 2;
                $schedule_template->repeat_time = $request->repeat_months;
                $schedule_template->occurs_on = $request->repeat_day_of_month;
            }
        }
        $schedule_template->save();
        Session::flash("success", "You have successfully stored template.");
        return redirect()->route("account");
    }


    // Close Account
    public function closeAccount()
    {
        $oldDbName =  DB::connection()->getDatabaseName();
        $default = [
            "driver" => env("DB_CONNECTION", "mysql"),
            "host" => env("DB_HOST"),
            "port" => env("DB_PORT"),
            "database" => env("DB_DATABASE"),
            "username" => env("DB_USERNAME"),
            "password" => env("DB_PASSWORD"),
            "charset" => "utf8mb4",
            "collation" => "utf8mb4_unicode_ci",
            "prefix" => "",
            "prefix_indexes" => true,
            "strict" => false,
            "engine" => null,
        ];

        Config::set('database.connections.mysql', $default);
        DB::purge('mysql');


        $user = User::where('database_name', $oldDbName)->first();
        $user->close_account = 0;
        $user->save();

        Session::flush();
        Auth::logout();
        return redirect()->route('login')->withError('Your account has been permanently closed, if you want to open it again please contact with Superadmin.');
    }


    // Send bulk email
    public function senBulkEmail(Request $request)
    {
        try {
            foreach ($request->to as $to) {
                $sendEmail = new BulkEmail();
                $sendEmail->to = $to;
                $sendEmail->subject = $request->subject;
                $sendEmail->message = $request->message;
                $sendEmail->type = $request->type;
                $sendEmail->save();

                $this->data["detais"] = [
                    "email" => $to,
                    "subject" => $request->subject,
                    "message" => $request->message,
                ];

                $email = $to;
                $subject = $request->subject;

                Mail::send("email.bulkemail", $this->data, function (
                    $message
                ) use ($email, $subject) {
                    $message
                        ->to($email)
                        ->from("info@gmail.com")
                        ->subject($subject);
                });
            }

            Session::flash("message", "You have successfully Send Bulk Email.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    // Inactive Users
    public function inactiveUsers()
    {
        $this->data["inactiveUser"] = User::where('close_account', 0)->get();
        return view("superadmin.admin.inactiveuser", $this->data);
    }

    // Activate account
    public function activateAccount($id)
    {
        try {
            $userDetails = User::where('id', $id)->first();
            $userDetails->close_account = 1;
            $userDetails->save();

            $this->data["detais"] = [
                "email" => $userDetails->email,

            ];

            $email = $userDetails->email;
            $subject = 'Activate Account';

            Mail::send("email.activeaccount", $this->data, function (
                $message
            ) use ($email, $subject) {
                $message
                    ->to($email)
                    ->from("info@gmail.com")
                    ->subject($subject);
            });

            Session::flash("message", "You have successfully Activate Account.");
            return back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }




    // Send bulk SMS
    public function senBulkSMS(Request $request)
    {
        try {
            foreach ($request->to as $to) {
                $sendSMS = new BulkSms();
                $sendSMS->to = $to;
                $sendSMS->message = $request->message;
                $sendSMS->type = $request->type;
                $sendSMS->save();
            }

            Session::flash("message", "You have successfully Send Bulk SMS.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    // List Bulk Emails
    public function listEmails($type)
    {
        $this->data['emails'] = BulkEmail::orderBy('id', 'DESC')->where('type', $type)->get();
        return view("staff.bulkemail", $this->data);
    }


    // List Bulk SMS
    public function listSMS($type)
    {
        $this->data['sms'] = BulkSms::orderBy('id', 'DESC')->where('type', $type)->get();

        return view("staff.bulksms", $this->data);
    }

    public function testCron()
    {
        $update = User::where('id', 1)->first();
        $update->first_name = 'Chauhan';
        $update->save();
    }
    // Update staff Settingscho
    public function settingsUpdate($sid, Request $request)
    {
        DB::table('staff_settings')->updateOrInsert(['staff_id' => $sid], [
            'teams' => implode(',', $request->team),
            'notify_timesheet_approval' => $request->notify_timesheet_approval,
            'available_for_rostering' => $request->available_for_rostering,
            'staff_visibleity' => $request->staff_visibleity,
            'private_notes' => $request->private_notes,
            'no_access' => $request->no_access,
            'account_owner' => $request->account_owner,

        ]);

        DB::table('role_user')
            ->where('user_id', $sid)
            ->update(['role_id' => $request->role_id]);

        Session::flash("message", "You have successfully updated.");
        return redirect()->route("staffDetails", [$sid]);
    }



    // For Roles
    public function roles()
    {

        $this->data['roles'] = Role::whereNotIn('id', [7, 8, 9, 10])->get();
        return view("role.index", $this->data);
        // return view("staff.expire_document", $this->data);
    }


    // Module Access
    public function moduleAccess($id)
    {
        $this->data['roleId'] = $id;
        $this->data['module'] = Module::where('role_id', $id)->pluck('module_key')->toArray();
        return view("role.view", $this->data);
    }

    // Module Access
    public function updateModulePermission(Request $request, $roleId)
    {

        Module::where('role_id', $roleId)->delete();


        foreach ($request['keyname'] as $routeName) {

            DB::table('modules')->Insert(['role_id' => $roleId, 'module_key' => $routeName]);
        }
        Session::flash("message", "You have successfully updated.");
        return redirect()->back();
    }


    public function GroupLoginUsers(Request $rquest)
    {

        $this->data["announces"] = Notification::orderBy(
            "id",
            "DESC"
        )->groupby('title')->get();

        $default_DBName = env("DB_DATABASE");
        $this->connectDB($default_DBName);

        //   return  $subUsers =  SubUser::get();
        //     $this->data["group_users"] =  $subUsers;

        return view("superadmin.groupusers.index");
    }
    public function images(Request $rquest)
    {

        $this->data["announces"] = Notification::orderBy(
            "id",
            "DESC"
        )->groupby('title')->get();

        $default_DBName = env("DB_DATABASE");
        $this->connectDB($default_DBName);

        //   return  $subUsers =  SubUser::get();
        //     $this->data["group_users"] =  $subUsers;

        return view("images.index");
    }

    public function createGroup(Request $rquest)
    {

        // $email = $rquest->input('email');
        // if (!$email) {
        //     return response()->json([]);
        // }
        // $users = SubUser::where('email', 'like', '%' . $email . '%')->get();
        // return response()->json($users);

        return view("superadmin.groupusers.groupCreate");
    }

    // public function searchByEmail(Request $request)
    // {

    //      $email = trim($request->input('email'));
    //     //$email = strtolower(trim($request->input('email')));
    //     if (!$email) {
    //         return response()->json([]);
    //     }
    //     $users = SubUser::where('email', $email)->get();
    //     // $users = SubUser::whereRaw('LOWER(email) = ?', [$email])->get();
    //     //$users = SubUser::whereRaw("TRIM(LOWER(email)) = ?", [$email])->get();

    //     if ($users->count() > 1) {
    //         return response()->json($users);
    //     }
    //    // info('aman' . $users);
    //     return response()->json([]);
    // }
    public function searchByEmail(Request $request)
    {
        $email = strtolower(trim($request->input('email')));

        if (!$email) {
            return response()->json([]);
        }

        // Fetch all users that match email (case-insensitive + trim)
        $users = SubUser::whereRaw("TRIM(LOWER(email)) = ?", [$email])->get();

        // Optional debug log
        // foreach ($users as $user) {
        //     info('Found Email: [' . $user->email . '] ID: ' . $user->id);
        // }

        if ($users->count() > 1) {
            return response()->json($users);
        }
        return response()->json([]);
    }

    public function getAllSubUsers()
    {
        $users = SubUser::select('id', 'email')->get();
        return response()->json($users);
    }

    public function saveSelectedUsers(Request $request)
    {

        $data =  $request->all();
        $userIds = $request->input('users', []);

        $email = $data['emails'][0];
        $data['user_id'] = $userIds;

        $default_DBName = env("DB_DATABASE");
        $this->connectDB($default_DBName);

        // DB::beginTransaction();

        $subUsers =  SubUser::whereIn('id', $data['user_id'])->where('email', $email)->get();

        $groupData = [
            'email' => $email,
            'user_id' =>  $userIds
        ];

        GroupLoginUser::updateOrCreate(['email' => $email], $groupData);

        foreach ($subUsers as $user) {
            // $user->password = Hash::make($data['password']);
            // $user->save();
            $this->connectDB($user->database_name);
            GroupLoginUser::updateOrCreate(['email' => $email], $groupData);

            // $userChild = User::find($user->id);
            // $userSubChild = SubUser::find($user->id);

            // if($userChild){
            //    $userChild->password = Hash::make($data['password']);
            // }
            // if($userSubChild){
            //     $userSubChild->password = Hash::make($data['password']);
            // }
            // $this->connectDB($default_DBName);

        }
        // DB::commit();
        return response()->json([
            'success' => true,
            'data' => [],
            'message' => "Maked group users successfully"

        ], 200);
    }
    public function createimage(Request $rquest)
    {

        // $email = $rquest->input('email');
        // if (!$email) {
        //     return response()->json([]);
        // }
        // $users = SubUser::where('email', 'like', '%' . $email . '%')->get();
        // return response()->json($users);

        return view("images.createimage");
    }
    public function storeimage(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        try {
            // Handle file upload
            if ($request->hasFile('image')) {
    $file = $request->file('image');
    $imgName = time() . '_' . $file->getClientOriginalName();
    $file->move(public_path('images'), $imgName);
    
    // Create image record
    $image = Image::create([
        'category' => $validated['category'],
        'position' => $validated['position'],
        'title' => $validated['title'],
        'description' => $validated['description'],
        //'file_path' => 'images/' . $imgName,  // Store relative path
        'image_name' => $imgName,
       // 'file_size' => $file->getSize(),
        //'file_type' => $file->getMimeType(),
       // 'status' => 'active',
    ]);


                return response()->json([
                    'success' => true,
                    'message' => 'Image uploaded successfully',
                    'data' => $image
                ], 201);
            }

            return response()->json([
                'success' => false,
                'message' => 'No image file provided'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading image: ' . $e->getMessage()
            ], 500);
        }
    }
    // app/Http/Controllers/Api/ImageController.php

public function imageindex(Request $request)
{
    try {
        $images = Image::query()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $images,
            'message' => 'Images retrieved successfully'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error retrieving images: ' . $e->getMessage()
        ], 500);
    }
}
public function imagedestroy($id)
{
    try {
        $image = Image::findOrFail($id);
        
        // Delete file from storage
       
        
        // Delete record
        $image->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error deleting image: ' . $e->getMessage()
        ], 500);
    }
}
}
