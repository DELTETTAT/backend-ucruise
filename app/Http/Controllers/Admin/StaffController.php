<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{DB, Auth, Mail, Hash, Session};
use App\Models\{Language, Leave, ReportHeadingCategory, Reschedule, Role, ScheduleCarer, ScheduleCarerRelocation, SubUser, StaffDocument, StaffKin, StaffNote, StaffPayrollSettings, StaffSettings, SubUserAddresse, Teams, User};

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $this->data["admins"] = User::whereHas("roles", function ($q) {
                $q->whereIn("name", ['carer', 'hr', 'office_support', 'coordinator', 'staff']);
            })
                ->orderBy("id", "DESC")
                ->get();
            return view("staff.index", $this->data);
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
        $this->data['roles'] = Role::whereNotIn('id', [8, 2, 4, 3, 9, 10])->get();
        return view("staff.add", $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            //store current DB name in temp variable
            $temp_DB_name = DB::connection()->getDatabaseName();

            //check if there existing staff in child DB for entered information
            $request->validate([
                "email" => "required|email|unique:users,email",
                "phone" => "required|unique:users,phone",
            ]);


            //connecting to parent DB
            $default_DBName = env("DB_DATABASE");
            $this->connectDB($default_DBName);

            //checking if there is existing staff with same information in parent DB
            $request->validate([
                "email" => "required|email|unique:staffs,email",
                "phone" => "required|unique:staffs,phone",
            ]);

            //creating staff in parent DB
            $unique_id = rand(1000, 9999) . str_pad(10, 3, STR_PAD_LEFT);

            $staff = new SubUser();

            $staff->salutation = @$request->salutation;
            $staff->first_name = $request->first_name;

            $staff->unique_id = $unique_id;
            $staff->email = $request->email;
            $staff->phone = $request->phone;
            $staff->mobile = $request->mobile;
            $staff->gender = $request->gender;
            $staff->dob = date('Y-m-d', strtotime($request->dob));
            $staff->employement_type = $request->employement_type;

            $staff->postal_code = $request->postalcode;


            $staff->company_name = Auth::user()->company_name;
            $staff->database_path = env("DB_HOST");
            $staff->database_name = $temp_DB_name;
            $staff->database_username = env("DB_USERNAME");
            $staff->database_password = env("DB_PASSWORD");

            $getRole =  $request->role;

            $rand = Str::random(10);
            $password = Hash::make($rand);
            $staff->password = $password;

            if ($request->type == 'carer') {
                $getRole = 'carer';
            }
            $admin = Role::where('name', 'staff')->first();

            if ($staff->save()) {
                if (!$staff->hasRole($getRole)) {
                    $staff->roles()->attach($admin);
                }
            }

            //connecting back to Child DB
            $this->connectDB($temp_DB_name);

            //create staff in users table in child DB

            $child_user = new User();

            $child_user->id = $staff->id;
            $child_user->salutation = @$request->salutation;
            $child_user->first_name = $request->first_name;

            $child_user->unique_id = $unique_id;
            $child_user->email = $request->email;
            $child_user->phone = $request->phone;
            $child_user->mobile = $request->mobile;
            $child_user->gender = $request->gender;
            $child_user->dob = date('Y-m-d', strtotime($request->dob));
            $child_user->employement_type = $request->employement_type;
            $child_user->address = $request->address;
            $child_user->postal_code = $request->postalcode;
            $child_user->latitude = $request->latitude;
            $child_user->longitude = $request->longitude;

            $child_user->company_name = $staff->company_name;
            $child_user->database_path = env("DB_HOST");
            $child_user->database_name = $temp_DB_name;
            $child_user->database_username = env("DB_USERNAME");
            $child_user->database_password = env("DB_PASSWORD");

            $child_user->password = $staff->password;
            $child_user->save();
            $getRole =  $request->role;
            if ($request->type == 'carer') {
                $getRole = 'carer';
            }
            $admin = Role::where('name', $getRole)->first();

            if ($child_user->save()) {
                if (!$child_user->hasRole($getRole)) {
                    $child_user->roles()->attach($admin);
                }
            }

            //creating staff in Staffs table in child db
            $child_staff = new SubUser();

            $child_staff->id = $staff->id;

            $child_staff->salutation = @$request->salutation;
            $child_staff->first_name = $request->first_name;

            $child_staff->unique_id = $unique_id;
            $child_staff->email = $request->email;
            $child_staff->phone = $request->phone;
            $child_staff->mobile = $request->mobile;
            $child_staff->gender = $request->gender;
            $child_staff->dob = date('Y-m-d', strtotime($request->dob));
            $child_staff->employement_type = $request->employement_type;
            $child_staff->postal_code = $request->postalcode;
            $child_staff->company_name = $staff->company_name;
            $child_staff->database_path = env("DB_HOST");
            $child_staff->database_name = $temp_DB_name;
            $child_staff->database_username = env("DB_USERNAME");
            $child_staff->database_password = env("DB_PASSWORD");

            $getRole =  $request->role;

            $child_staff->password = $staff->password;

            if ($request->type == 'carer') {
                $getRole = 'carer';
            }
            $admin = Role::where('name', $getRole)->first();

            if ($child_staff->save()) {
                if (!$child_staff->hasRole($getRole)) {
                    $child_staff->roles()->attach($admin);
                }
            }

            $this->data["detais"] = [
                "email" => $request->email,
                "pass" => $rand,
                "unique_id" => $unique_id,
            ];
            $email = $request->email;
            Mail::send("email.adminlogin", $this->data, function (
                $message
            ) use ($email) {
                $message
                    ->to($email)
                    ->from("info@gmail.com")
                    ->subject("Staff Login Details");
            });
            $sub_user = SubUser::find($child_staff->id);

            if ($sub_user) {
                $sub_user_address = SubUserAddresse::where('sub_user_id', $sub_user->id)->whereNull('end_date')->first();

                if ($sub_user_address) {
                    if ($sub_user_address->start_date == date('Y-m-d')) {

                        $sub_user_address->sub_user_id = $sub_user->id;
                        $sub_user_address->address = $request->address;
                        $sub_user_address->latitude = $request->latitude;
                        $sub_user_address->longitude = $request->longitude;
                    } else {
                        $sub_user_address->end_date = date('Y-m-d');
                        $sub_new_address = new SubUserAddresse();

                        $sub_new_address->sub_user_id = $sub_user->id;
                        $sub_new_address->address = $request->address;
                        $sub_new_address->latitude = $request->latitude;
                        $sub_new_address->longitude = $request->longitude;
                        $sub_new_address->start_date = date('Y-m-d');
                        $sub_new_address->save();
                    }
                    $sub_user_address->update();
                } else {

                    $sub_new_address = new SubUserAddresse();

                    $sub_new_address->sub_user_id = $sub_user->id;
                    $sub_new_address->address = $request->address;
                    $sub_new_address->latitude = $request->latitude;
                    $sub_new_address->longitude = $request->longitude;
                    $sub_new_address->start_date = date('Y-m-d');

                    $sub_new_address->save();
                }
            }


            Session::flash("message", "You have successfully added.");
            return redirect()->route("staff.list");
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        if ($id) {
            $user = User::find($id);
            $sub_user = SubUser::where('email', $user->email)->first();
        }

        $this->data['teams'] = Teams::get();
        $this->data['roles'] = Role::whereNotIn('id', [2, 3, 8, 9, 10])->get();
        $this->data["csetting"] = StaffSettings::where('staff_id', $id)->first();
        $this->data["adf"] = StaffPayrollSettings::where('staff_id', $id)->first();
        $this->data["kin"] = StaffKin::where('staff_id', $id)->first();
        $this->data["stf"] = StaffNote::where('staff_id', $id)->first();
        $this->data["show"] = User::where('id', $id)->first();
        $this->data['docoments'] = StaffDocument::where('staff_id', $id)->get();
        if ($sub_user) {
            $this->data['reschedules'] = Reschedule::where('user_id', $sub_user->id)->get();
            $this->data['leaveRequests'] = Leave::where('staff_id', $sub_user->id)->get();
            $this->data['scheduleCarerRelocations'] = ScheduleCarerRelocation::where('staff_id', $sub_user->id)->get();
        }

        $this->data['reportHeadingCategory'] = ReportHeadingCategory::with('catHeadings')->where('category_name', 'Compliance')->orderBy('id', 'ASC')->get();

        return view("staff.view", $this->data);
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
            $this->data['edit'] = User::where('id', $id)->first();
            $this->data['language'] = Language::get();
            return view("staff.edit", $this->data);
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

            // dd($request->salutation);
            $temp_DB_name = DB::connection()->getDatabaseName();
            //connecting to parent DB
            $default_DBName = env("DB_DATABASE");
            //dd($default_DBName);
            $this->connectDB($default_DBName);

            //dd($id);
            $update = SubUser::where('email', $request->email)->first();
            //dd($update);
            $update->salutation = @$request->salutation;
            $update->first_name = $request->first_name;
            $update->middle_name = $request->middle_name;
            $update->last_name = $request->last_name;
            $update->display_name = $request->display_name;
            $update->gender = $request->gender;
            $update->dob = date('Y-m-d', strtotime($request->dob));


            $update->appartment_number = $request->appartment_number;
            $update->mobile = $request->mobile;
            $update->phone = $request->phone;
            $update->employement_type = $request->employement_type;

            $update->language_spoken = implode(',', $request->language_spoken);

            if ($request->file) {

                $path = public_path('images/');
                !is_dir($path) &&
                    mkdir($path, 0777, true);

                $filename = time() . '.' . $request->file->extension();
                $request->file->move($path, $filename);
                //\DB::table('company_details')->updateOrInsert(['id' => 1],['logo' => $filename]);
                $update->profile_image = $filename;
            }

            $update->save();
            //connecting back to Child DB
            $this->connectDB($temp_DB_name);



            $update_user = User::where('email', $request->email)->first();

            $update_user->salutation = @$request->salutation;
            $update_user->first_name = $request->first_name;
            $update_user->middle_name = $request->middle_name;
            $update_user->last_name = $request->last_name;
            $update_user->display_name = $request->display_name;
            $update_user->gender = $request->gender;
            $update_user->dob = date('Y-m-d', strtotime($request->dob));
            $update_user->address = $request->address;
            $update_user->latitude = $request->latitude;
            $update_user->longitude = $request->longitude;

            $update_user->appartment_number = $request->appartment_number;
            $update_user->mobile = $request->mobile;
            $update_user->phone = $request->phone;
            $update_user->employement_type = $request->employement_type;

            $update_user->language_spoken = implode(',', $request->language_spoken);

            if ($request->file) {

                $path = public_path('images/');
                !is_dir($path) &&
                    mkdir($path, 0777, true);

                $filename = time() . '.' . $request->file->extension();
                $request->file->move($path, $filename);
                //\DB::table('company_details')->updateOrInsert(['id' => 1],['logo' => $filename]);
                $update_user->profile_image = $filename;
            }

            $update_user->save();

            $child_update = SubUser::where('email', $request->email)->first();
            $child_update->salutation = @$request->salutation;
            $child_update->first_name = $request->first_name;
            $child_update->middle_name = $request->middle_name;
            $child_update->last_name = $request->last_name;
            $child_update->display_name = $request->display_name;
            $child_update->gender = $request->gender;
            $child_update->dob = date('Y-m-d', strtotime($request->dob));


            $child_update->appartment_number = $request->appartment_number;
            $child_update->mobile = $request->mobile;
            $child_update->phone = $request->phone;
            $child_update->employement_type = $request->employement_type;

            $child_update->language_spoken = implode(',', $request->language_spoken);

            if ($request->file) {

                $path = public_path('images/');
                !is_dir($path) &&
                    mkdir($path, 0777, true);

                $filename = time() . '.' . $request->file->extension();
                $request->file->move($path, $filename);
                //\DB::table('company_details')->updateOrInsert(['id' => 1],['logo' => $filename]);
                $child_update->profile_image = $filename;
            }
            $child_update->save();
            $sub_user = SubUser::find($child_update->id);

            if ($sub_user) {

                $sub_user_address = SubUserAddresse::where('sub_user_id', $sub_user->id)->whereNull('end_date')->first();

                if ($sub_user_address) {
                    if ($sub_user_address->start_date == date('Y-m-d')) {

                        $sub_user_address->sub_user_id = $sub_user->id;
                        $sub_user_address->address = $request->address;
                        $sub_user_address->latitude = $request->latitude;
                        $sub_user_address->longitude = $request->longitude;
                    } else if ($sub_user_address->start_date > date('Y-m-d')) {
                    } else {
                        $sub_user_address->end_date = date('Y-m-d');
                        $sub_new_address = new SubUserAddresse();

                        $sub_new_address->sub_user_id = $sub_user->id;
                        $sub_new_address->address = $request->address;
                        $sub_new_address->latitude = $request->latitude;
                        $sub_new_address->longitude = $request->longitude;
                        $sub_new_address->start_date = date('Y-m-d');
                        $sub_new_address->save();
                    }
                    $sub_user_address->update();
                } else {

                    $sub_new_address = new SubUserAddresse();

                    $sub_new_address->sub_user_id = $sub_user->id;
                    $sub_new_address->address = $request->address;
                    $sub_new_address->latitude = $request->latitude;
                    $sub_new_address->longitude = $request->longitude;
                    $sub_new_address->start_date = date('Y-m-d');

                    $sub_new_address->save();
                }
            }



            Session::flash("message", "You have successfully updated.");
            return redirect()->route("staff.list");
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
    public function destroy($id)
    {
        //
    }

    // Update Payroll Setings
    public function staffPayrollSetting($sid, Request $request)
    {
        DB::table('staff_payroll_settings')->updateOrInsert(['staff_id' => $sid], [

            'pay_group' => $request->pay_group,
            'daily_hours' => $request->daily_hours,
            'weekly_hours' => $request->weekly_hours,
            'external_system_identifier' => $request->external_system_identifier,


        ]);

        Session::flash("message", "You have successfully updated.");
        return redirect()->route("staffDetails", [$sid]);
    }

    // Update  staff Kin
    public function updateStaffKin($sid, Request $request)
    {
        DB::table('staff_kin')->updateOrInsert(['staff_id' => $sid], [

            'name' => $request->name,
            'relation' => $request->relation,
            'contact' => $request->contact,
            'email' => $request->email,


        ]);

        Session::flash("message", "You have successfully updated.");
        return redirect()->route("staffDetails", [$sid]);
    }

    // Update  staff Note
    public function updateStaffNote($sid, Request $request)
    {
        DB::table('staff_notes')->updateOrInsert(['staff_id' => $sid], [

            'private_info' => $request->private_info


        ]);

        Session::flash("message", "You have successfully updated.");
        return redirect()->route("staffDetails", [$sid]);
    }

    //   List staff doccument
    public function staffDocuments($id)
    {
        $this->data['docoments'] = StaffDocument::orderBy('id', 'DESC')->where('staff_id', $id)->groupby('name')->get();
        $this->data['reportHeadingCategory'] = ReportHeadingCategory::with('catHeadings')->orderBy('id', 'ASC')->get();
        $this->data['cId'] = $id;
        return view("staff.list_documents", $this->data);
    }

    // upload staff document
    public function uploadStaffDocument($id, Request $request)
    {

        if ($request->file) {
            $type = $request->file('file')->extension();

            $path = public_path('images/');
            !is_dir($path) &&
                mkdir($path, 0777, true);

            $filename = time() . '.' . $request->file->extension();
            $request->file->move($path, $filename);
            //\DB::table('company_details')->updateOrInsert(['id' => 1],['logo' => $filename]);

            $storeDoc = new StaffDocument();
            $storeDoc->category = $request->category;
            $storeDoc->staff_id = $id;
            $storeDoc->type = $type;
            $storeDoc->name = $filename;
            $storeDoc->save();
            Session::flash("message", "You have successfully addedd.");
            return redirect()->back();
        }
    }

    // Update Staff doc category
    public function updateStaffDocCategory($id, Request $request)
    {


        $update = StaffDocument::where('id', $id)->first();
        if ($request->category) {
            $update->category = $request->category;
        }

        if ($request->staff_visibleity) {
            $update->staff_visibleity = $request->staff_visibleity;
        }

        if ($request->expire) {
            $update->expire = date('Y-m-d', strtotime($request->expire));
        }


        if ($request->file) {
            $type = $request->file('file')->extension();

            $path = public_path('images/');
            !is_dir($path) &&
                mkdir($path, 0777, true);

            $filename = time() . '.' . $request->file->extension();
            $request->file->move($path, $filename);
            //\DB::table('company_details')->updateOrInsert(['id' => 1],['logo' => $filename]);

            $update->type = $type;
            $update->name = $filename;
        }


        $update->save();
        Session::flash("message", "You have successfully updated.");
        return redirect()->back();
    }

    public function updateStaffNoExpireation($id, Request $request)
    {

        $update = StaffDocument::where('id', $id)->first();
        $update->no_expireation = $request->no_expireation;
        $update->save();
        Session::flash("message", "You have successfully updated.");
        return redirect()->back();
    }


    // List all Expire Documents
    public function expireStaffDocuments()
    {

        $this->data['expireDoc'] = StaffDocument::with('staff')
            ->whereDate('expire', '<', date('Y-m-d'))
            ->orwhereNull('expire')
            ->whereNull('no_expireation')
            ->get();
        return view("staff.expire_document", $this->data);
    }

    // Archived Staff
    public function arcchiveStaff()
    {
        $this->data["arcchivedStaff"] = User::whereHas("roles", function ($q) {
            $q->whereIn("name", ['archived_staff']);
        })
            ->orderBy("id", "DESC")
            ->get();
        return view("staff.archive", $this->data);
    }

    // Archive Staff
    public function staffArchiveAccount($id)
    {
        DB::table('role_user')
            ->where('user_id', $id)
            ->update(['role_id' => 10]);


        Session::flash("message", "You have successfully archive account.");
        return redirect()->route("staff.list");
    }

    // Unarchive Staff
    public function unurchiveStaff($id)
    {
        DB::table('role_user')
            ->where('user_id', $id)
            ->update(['role_id' => 4]);


        Session::flash("message", "You have successfully archive account.");
        return redirect()->back();
    }

    // Export staff data in csv file
    public function exportStaff()
    {
        try {
            $delimiter = ",";
            $filename = "staff_" . date('Y-m-d') . ".csv";

            // Create a file pointer 
            $f = fopen('php://memory', 'w');

            // Set column headers 
            $fields = array('ID', 'FIRST NAME', 'EMAIL', 'GENDER', 'PHONE', 'ROLE', 'ADDRESS', 'Employement Type');
            fputcsv($f, $fields, $delimiter);

            // Output each row of the data, format line as csv and write to file pointer 
            $this->data["admins"] = User::whereHas("roles", function ($q) {
                $q->whereIn("name", ["admin", 'carer', 'hr']);
            })
                ->orderBy("id", "DESC")
                ->get();

            foreach ($this->data["admins"] as $key => $data) {

                $lineData = array(
                    $key + 1,
                    $data->first_name,
                    // $data->last_name,
                    $data->email,
                    $data->gender,
                    $data->phone,
                    ucfirst($data->roles[0]->name),
                    $data->address,
                    $data->employement_type
                );
                fputcsv($f, $lineData, $delimiter);
            }

            // Move back to beginning of file 
            fseek($f, 0);

            // Set headers to download file rather than displayed 
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '";');

            //output all remaining data on a file pointer 
            fpassthru($f);
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }
    public function deleteStaff($id)
    {
        try {
            $user = User::find($id);
            $sub_user = SubUser::where('email', $user->email)->first();

            $staffShifts = ScheduleCarer::where('carer_id', $sub_user->id)->exists();
            if ($staffShifts) {
                Session::flash("error", "Staff exists in the shifts");
                return redirect()->back();
            } else {
                $temp_DB_name = DB::connection()->getDatabaseName();
                $default_DBName = env("DB_DATABASE");
                $this->connectDB($default_DBName);
                SubUser::where('email', $user->email)->delete();
                $this->connectDB($temp_DB_name);
                $sub_user->delete();
                $user->delete();
                Session::flash("message", "Staff deleted successfully");
                return redirect()->route('login');
            }
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }
}
