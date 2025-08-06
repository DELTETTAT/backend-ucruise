<?php

namespace App\Http\Controllers\Superadmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\{
    Language,
    User,
    Role,
    Clienttype,
    PriceBook,
    Teams,
    Clientsettings,
    ClientAdditionalInfo,
    ClientDocuments,
    DocCategory,
    Schedule,
    SubUser,
    SubUserAddresse,
    Vehicle
};
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{Auth, DB, Hash, Mail, Session};

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $date=date('Y-m-d');
        $this->data["drivers"] = SubUser::join('sub_user_addresses', 'sub_users.id', '=', 'sub_user_addresses.sub_user_id')
         
        ->whereHas("roles", function ($q) {
            $q->whereIn("name", ['driver']);
        })
        ->where(function ($query) use ($date) {
            $query->whereDate('sub_user_addresses.start_date', '<=', $date)
                ->where(function ($query) use ($date) {
                    $query->whereDate('sub_user_addresses.end_date', '>', $date)
                        ->orWhereNull('sub_user_addresses.end_date');
                });
        })
        ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address')
        ->orderBy("sub_users.id", "DESC")
        ->get();
        return view("clients.index", $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->data['language'] = Language::get();
        return view("clients.add", $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());

        try {

            //store current DB name in temp variable
            $temp_DB_name = DB::connection()->getDatabaseName();

            //check if there existing driver in child DB for entered information
            $request->validate([
                "email" => "required|email|unique:sub_users,email",
            ]);


            //connecting to parent DB
            $default_DBName = env("DB_DATABASE");
            $this->connectDB($default_DBName);

            //checking if there is existing driver with same information in parent DB
            $request->validate([
                "email" => "required|email|unique:sub_users,email",
            ]);

            //creating driver in parent DB
            $unique_id = rand(1000, 9999) . str_pad(10, 3, STR_PAD_LEFT);

            $driver = new SubUser();
            $driver->salutation = @$request->salutation;
            $driver->first_name = $request->first_name;
            $driver->middle_name = $request->middle_name;
            $driver->last_name = $request->last_name;
            $driver->display_name = $request->display_name;
            $driver->gender = $request->gender;
            $driver->dob = date('Y-m-d', strtotime($request->dob));

            $driver->appartment_number = $request->appartment_number;
            $driver->mobile = $request->mobile;
            $driver->phone = $request->phone;
            $driver->email = $request->email;
            $driver->religion = $request->religion;
            $driver->marital_status = $request->marital_status;
            $driver->nationality = $request->nationality;
            $driver->language_spoken = implode(',', $request->language_spoken);
            $rand = Str::random(10);
            $password = Hash::make($rand);
            $driver->password = $password;


            $driver->company_name = Auth::user()->company_name;

            $driver->database_path = env("DB_HOST");
            $driver->database_name = $temp_DB_name;
            $driver->database_username = env("DB_USERNAME");
            $driver->database_password = env("DB_PASSWORD");

            if ($request->profileImage) {
                $path = public_path('images/');
                !is_dir($path) &&
                    mkdir($path, 0777, true);

                $profilefilename = time() . '.' . $request->profileImage->extension();
                $request->profileImage->move($path, $profilefilename);
                //\DB::table('company_details')->updateOrInsert(['id' => 1],['logo' => $filename]);
                $driver->profile_image = $profilefilename;
            }

            $driver->save();

            $role = Role::where("name", "driver")->first();

            // Manage Role
            if (!$driver->hasRole("driver")) {
                $driver->roles()->attach($role);
            }


            //connecting back to Child DB
            $this->connectDB($temp_DB_name);

            //creating driver in Staffs table in child db
            $child_driver = new SubUser();
            $child_driver->id = $driver->id;

            $child_driver->salutation = @$request->salutation;
            $child_driver->first_name = $request->first_name;
            $child_driver->middle_name = $request->middle_name;
            $child_driver->last_name = $request->last_name;
            $child_driver->display_name = $request->display_name;
            $child_driver->gender = $request->gender;
            $child_driver->dob = date('Y-m-d', strtotime($request->dob));

            $child_driver->appartment_number = $request->appartment_number;
            $child_driver->mobile = $request->mobile;
            $child_driver->phone = $request->phone;
            $child_driver->email = $request->email;
            $child_driver->religion = $request->religion;
            $child_driver->marital_status = $request->marital_status;
            $child_driver->nationality = $request->nationality;
            $child_driver->language_spoken = implode(',', $request->language_spoken);

            $child_driver->password = $password;

            $child_driver->company_name = $driver->company_name;
            $child_driver->database_path = env("DB_HOST");
            $child_driver->database_name = $temp_DB_name;
            $child_driver->database_username = env("DB_USERNAME");
            $child_driver->database_password = env("DB_PASSWORD");

            if ($request->profileImage) {
                $child_driver->profile_image = $profilefilename;
            }

            $child_driver->save();

            $role = Role::where("name", "driver")->first();

            // Manage Role
            if (!$child_driver->hasRole("driver")) {
                $child_driver->roles()->attach($role);
            }

            $this->data["detais"] = [
                "email" => $request->email,
                "pass" => $rand,
            ];
            $email = $request->email;

            Mail::send("email.adminlogin", $this->data, function (
                $message
            ) use ($email) {
                $message
                    ->to($email)
                    ->from("info@gmail.com")
                    ->subject("Client Login Details");
            });
            $sub_user = SubUser::find($child_driver->id);

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
            //Storing vehicle info
            if ($sub_user->id) {
                $vehicle = new Vehicle();
                $vehicle->name = $request->name;
                $vehicle->driver_id = $sub_user->id;
                $vehicle->description = $request->description;
                $vehicle->chasis_no = $request->chasis_no;
                $vehicle->seats = $request->seats;
                $vehicle->registration_no = $request->registration_no;
                $vehicle->vehicle_no = $request->vehicle_no;
                $vehicle->color = $request->color;
                $vehicle->fare = $request->fare;
                if ($request->vehicleImage) {

                    $path = public_path('images/vehicles');
                    !is_dir($path) &&
                        mkdir($path, 0777, true);

                    $vehiclefilename = time() . '.' . $request->vehicleImage->extension();
                    $request->vehicleImage->move($path, $vehiclefilename);
                    //\DB::table('company_details')->updateOrInsert(['id' => 1],['logo' => $filename]);
                    $vehicle->image = $vehiclefilename;
                }
                $vehicle->save();
            }


            Session::flash("message", "You have successfully added.");
            return redirect()->route("clients.index");
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }


    public function storeTest(Request $request)
    {
        $request->validate([
            "email" => "required|email|unique:users,email"
        ]);

        $store = new User();
        $store->first_name = $request->first_name;
        $store->middle_name = $request->middle_name;
        $store->last_name = $request->last_name;
        $store->display_name = $request->display_name;
        $store->gender = $request->gender;
        $store->dob = date('Y-m-d', strtotime($request->dob));
        $store->address = $request->address;

        $store->appartment_number = $request->appartment_number;
        $store->mobile = $request->mobile;
        $store->phone = $request->phone;
        $store->email = $request->email;
        $store->religion = $request->religion;
        $store->marital_status = $request->marital_status;
        $store->nationality = $request->nationality;
        $store->language_spoken = implode(',', $request->language_spoken);
        $rand = Str::random(10);
        $password = Hash::make($rand);
        $store->password = $password;
        $store->save();

        $driver = Role::where("name", "driver")->first();

        // Manage Role
        if (!$store->hasRole("driver")) {
            $store->roles()->attach($driver);
        }


        $this->data["detais"] = [
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
                ->from("info@gmail.com")
                ->subject("Client Login Details");
        });

        Session::flash("message", "You have successfully added.");
        return redirect()->route("clients.index");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $this->data["clienttype"] = Clienttype::get();
        $this->data["priceBook"] = PriceBook::get();
        $this->data['teams'] = Teams::get();
        $date = date('Y-m-d');
        $this->data['show'] = SubUser::join('sub_user_addresses', 'sub_users.id', '=', 'sub_user_addresses.sub_user_id')
            ->where('sub_users.id', $id)
            ->whereHas("roles", function ($q) {
                $q->whereIn("name", ['driver']);
            })
            ->where(function ($query) use ($date) {
                $query->whereDate('sub_user_addresses.start_date', '<=', $date)
                    ->where(function ($query) use ($date) {
                        $query->whereDate('sub_user_addresses.end_date', '>', $date)
                            ->orWhereNull('sub_user_addresses.end_date');
                    });
            })
            ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address')
            ->with('vehicle')
            ->first();
        //$this->data["show"] = SubUser::with('vehicle')->where('id', $id)->first();

        $this->data["csetting"] = Clientsettings::where('client_id', $id)->first();
        $this->data["adf"] = ClientAdditionalInfo::where('client_id', $id)->first();
        $this->data['docoments'] = ClientDocuments::where('client_id', $id)->orderBy('id', 'DESC')->get();
        return view("clients.view", $this->data);
        //echo '<pre>';print_r($this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $date = date('Y-m-d');
        $this->data['edit'] = SubUser::join('sub_user_addresses', 'sub_users.id', '=', 'sub_user_addresses.sub_user_id')
            ->where('sub_users.id', $id)
            ->whereHas("roles", function ($q) {
                $q->whereIn("name", ['driver']);
            })
            ->where(function ($query) use ($date) {
                $query->whereDate('sub_user_addresses.start_date', '<=', $date)
                    ->where(function ($query) use ($date) {
                        $query->whereDate('sub_user_addresses.end_date', '>', $date)
                            ->orWhereNull('sub_user_addresses.end_date');
                    });
            })
            ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address')
            
            ->with('vehicle')
            ->first();
        $this->data['language'] = Language::get();
        return view("clients.edit", $this->data);
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

            //store current DB name in temp variable
            $temp_DB_name = DB::connection()->getDatabaseName();

            //connecting to parent DB
            $default_DBName = env("DB_DATABASE");
            $this->connectDB($default_DBName);

            //updating driver in parent DB
            $update = SubUser::where('id', $id)->first();
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
            $update->religion = $request->religion;
            $update->marital_status = $request->marital_status;
            $update->nationality = $request->nationality;
            $update->language_spoken = implode(',', $request->language_spoken);

            if ($request->profileImage) {

                $path = public_path('images/');
                !is_dir($path) &&
                    mkdir($path, 0777, true);

                $profilefilename = time() . '.' . $request->profileImage->extension();
                $request->profileImage->move($path, $profilefilename);
                $update->profile_image = $profilefilename;
            }

            $update->save();

            //connecting back to Child DB
            $this->connectDB($temp_DB_name);

            //updating driver in Staffs table in child db
            $child_update = SubUser::where('id', $id)->first();
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
            $child_update->religion = $request->religion;
            $child_update->marital_status = $request->marital_status;
            $child_update->nationality = $request->nationality;
            $child_update->language_spoken = implode(',', $request->language_spoken);

            if ($request->profileImage) {

                $path = public_path('images/');
                !is_dir($path) &&
                    mkdir($path, 0777, true);

                $profilefilename = time() . '.' . $request->profileImage->extension();
                $request->profileImage->move($path, $profilefilename);
                $child_update->profile_image = $profilefilename;
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

                $vehicle = Vehicle::where('driver_id', $sub_user->id)->first();
                if ($vehicle) {

                    $vehicle->name = $request->name;

                    $vehicle->seats = $request->seats;
                    $vehicle->description = $request->description;
                    $vehicle->chasis_no = $request->chasis_no;
                    $vehicle->registration_no = $request->registration_no;
                    $vehicle->vehicle_no = $request->vehicle_no;
                    $vehicle->color = $request->color;
                    $vehicle->fare = $request->fare;
                    if ($request->vehicleImage) {

                        $path = public_path('images/vehicles');
                        !is_dir($path) &&
                            mkdir($path, 0777, true);

                        $vehiclefilename = time() . '.' . $request->vehicleImage->extension();
                        $request->vehicleImage->move($path, $vehiclefilename);
                        //\DB::table('company_details')->updateOrInsert(['id' => 1],['logo' => $filename]);
                        $vehicle->image = $vehiclefilename;
                    }
                    $vehicle->update();
                } else {
                    $vehicle = new Vehicle();
                    $vehicle->name = $request->name;
                    $vehicle->driver_id = $sub_user->id;
                    $vehicle->description = $request->description;
                    $vehicle->chasis_no = $request->chasis_no;
                    $vehicle->seats = $request->seats;
                    $vehicle->registration_no = $request->registration_no;
                    $vehicle->vehicle_no = $request->vehicle_no;
                    $vehicle->color = $request->color;
                    $vehicle->fare = $request->fare;
                    if ($request->vehicleImage) {

                        $path = public_path('images/vehicles');
                        !is_dir($path) &&
                            mkdir($path, 0777, true);

                        $vehiclefilename = time() . '.' . $request->vehicleImage->extension();
                        $request->vehicleImage->move($path, $vehiclefilename);
                        //\DB::table('company_details')->updateOrInsert(['id' => 1],['logo' => $filename]);
                        $vehicle->image = $vehiclefilename;
                    }
                    $vehicle->save();
                }
            }


            Session::flash("message", "You have successfully updated.");
            return redirect()->route("clients.index");
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
    public function clientSettingStore($id, Request $request)
    {
        DB::table('clientsettings')->updateOrInsert(['client_id' => $id], [
            'NDIS_number' => $request->NDIS_number,
            'recipient_id' => $request->recipient_id,
            'reference_number' => $request->reference_number,
            'custom_field' => $request->custom_field,
            'po_number' => $request->po_number,
            'client_type' => $request->client_type,
            'price_book' => $request->price_book,
            'team' => implode(',', $request->team),
            'progress_note' => $request->progress_note,
            'enable_sms_reminder' => $request->enable_sms_reminder,
            'invoice_travel' => $request->invoice_travel,
        ]);

        Session::flash("message", "You have successfully added.");
        return redirect()->route("clients.show", [$id]);
    }


    public function clientAdditionalInfo1($id, Request $request)
    {
        DB::table('client_additional_infos')->updateOrInsert(['client_id' => $id], [
            'private_info' => $request->private_info,
            'review_date' => date('Y-m-d', strtotime($request->review_date)),

        ]);

        Session::flash("message", "You have successfully added.");
        return redirect()->route("clients.show", [$id]);
    }


    // Client archive account
    public function clientArchiveAccount($id)
    {
        DB::table('role_sub_user')
            ->where('sub_user_id', $id)
            ->update(['role_id' => 12]);


        Session::flash("message", "You have successfully archive account.");
        return redirect()->route("clients.index");
    }

    // Archived Client
    public function arcchiveClients()
    {
        $this->data["arcchivedClient"] = SubUser::whereHas("roles", function ($q) {
            $q->whereIn("name", ['archived_driver']);
        })
            ->orderBy("id", "DESC")
            ->get();
        return view("clients.archive", $this->data);
    }

    // UnArchive Client
    public function unurchiveClient($id)
    {
        DB::table('role_sub_user')
            ->where('sub_user_id', $id)
            ->update(['role_id' => 5]);


        Session::flash("message", "You have successfully archive account.");
        return redirect()->back();
    }



    // Client Documents
    public function clientDocuments($id)
    {

        $this->data['docoments'] = ClientDocuments::orderBy('id', 'DESC')->where('client_id', $id)->get();
        $this->data['docCategory'] = DocCategory::get();
        $this->data['cId'] = $id;
        return view("clients.list_documents", $this->data);
    }

    // Client Documents
    public function uploadClientDocument($id, Request $request)
    {

        if ($request->file) {
            $type = $request->file('file')->extension();

            $path = public_path('images/');
            !is_dir($path) &&
                mkdir($path, 0777, true);

            $filename = time() . '.' . $request->file->extension();
            $request->file->move($path, $filename);
            //\DB::table('company_details')->updateOrInsert(['id' => 1],['logo' => $filename]);

            $storeDoc = new ClientDocuments();
            $storeDoc->client_id = $id;
            $storeDoc->type = $type;
            $storeDoc->name = $filename;
            $storeDoc->save();
            Session::flash("message", "You have successfully addedd.");
            return redirect()->back();
        }
    }

    // Update client doc category
    public function updateClientDocCategory($id, Request $request)
    {


        $update = ClientDocuments::where('id', $id)->first();
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

    public function updateClientNoExpireation($id, Request $request)
    {

        $update = ClientDocuments::where('id', $id)->first();
        $update->no_expireation = $request->no_expireation;
        $update->save();
        Session::flash("message", "You have successfully updated.");
        return redirect()->back();
    }


    // List all Expire Documents
    public function expireClientDocuments()
    {

        $this->data['expireDoc'] = ClientDocuments::with('clients')
            ->whereDate('expire', '<', date('Y-m-d'))
            ->orwhereNull('expire')
            ->whereNull('no_expireation')
            ->get();
        return view("clients.expire_document", $this->data);
    }



    public function newClient()
    {
        return view("clients.new");
    }
    public function deleteDriver($id)
    {
        try {

            $sub_user = SubUser::find($id);

            $driver = Schedule::where('driver_id', $sub_user->id)->exists();
            if ($driver) {
                Session::flash("error", "Driver exists in the shifts");
                return redirect()->back();
            } else {
                $temp_DB_name = DB::connection()->getDatabaseName();
                $default_DBName = env("DB_DATABASE");
                $this->connectDB($default_DBName);
                SubUser::where('email', $sub_user->email)->delete();
                $this->connectDB($temp_DB_name);
                $sub_user->delete();
                Session::flash("message", "Driver deleted successfully");
                return redirect()->route('login');
            }
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }
}
