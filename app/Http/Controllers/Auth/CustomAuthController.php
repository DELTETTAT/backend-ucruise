<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Notification;
use App\Models\Reminder;
use App\Models\SubUser;
use Illuminate\Http\Request;

use Hash;
use Session;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use DB;


class CustomAuthController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            Session::put('id', Auth::user()->id);
            return redirect()->route("admin.dashboard");
        } else {
            //dd(33);
            return view('auth.login');
        }
    }

    public function customLogin(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        $user = User::where(['email' => $request->email])->first();
        $staff = SubUser::where(['email' => $request->email])->first();
        if (!$user & !$staff) {
            \Session::flash("error", "Please Enter Valid Email.");
            return redirect()->back();
        }

        if ($staff) {
            $user = $staff;
        }


        if ($user->close_account == 0) {
            return redirect()->back()->withError('Your account has been permanently closed, if you want to open it again please contact with Superadmin.');
        }

        if ($user->hasRole('superadmin')) {
        } elseif ($user->hasRole('admin') && $user->status == 1) {
        } elseif ($user->hasRole('staff') && $user->status == 1) {
        } else {
            return redirect()->back()->withError('Access Denied for this user');
        }
        $credentials = $request->only('email', 'password');
        if ($user->hasRole('admin')) {

            if (Auth::guard('web')->attempt($credentials)) {
                 
                return redirect()->intended('admin/scheduler')
                    ->withSuccess('Signed in');

            }
        } else if ($user->hasRole('superadmin')) {
            if (Auth::guard('web')->attempt($credentials)) {
                return redirect()->intended('admin/dashboard')
                    ->withSuccess('Signed in');
            }
        } else {
            if (Auth::guard('staff')->attempt($credentials)) {
                return redirect()->intended('admin/scheduler')
                    ->withSuccess('Signed in');
            }
        }


        return redirect()->back()->withError('Login details are not valid');
    }

    public function registration()
    {
        return view('auth.registration');
    }

    public function customRegistration(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $data = $request->all();
        $check = $this->create($data);

        return redirect("dashboard")->withSuccess('You have signed-in');
    }

    public function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
    }

    public function dashboard()
    {
        if (Auth::check() || Auth::guard('staff')->check()) {
            $this->data['adminCount'] = User::whereHas('roles', function ($q) {
                $q->where('name', 'admin');
            })->count();
            return view('superadmin.dashboard', $this->data);
        }

        return redirect()->route('login')->withSuccess('You are not allowed to access');
    }

    public function signOut()
    {
        Session::flush();
        Auth::logout();
        return redirect()->route('login');
    }


    // SubUser login
    public function staff(Request $request)
    {
        if ($request->all()) {
            // $dnname = User::pluck('database_name')->toArray();
            // //echo '<pre>';print_r(array_filter($dnname));die;

            // $valueToCheck = $request->unique_id;

            // $databaseConnections = array_filter($dnname);

            // foreach ($databaseConnections as $connectionName) {
            //     $exists = DB::connection($connectionName)
            //                 ->table('users')
            //                 ->where('unique_id', $valueToCheck)
            //                 ->exists();

            //     if ($exists) {
            //         // Value exists in the current database connection
            //         echo "Value exists in $connectionName.\n";
            //     } else {
            //         // Value doesn't exist in the current database connection
            //         echo "Value does not exist in $connectionName.\n";
            //     }
            // }


            $users = User::get(1);
            //  echo $dbname = DB::connection()->getDatabaseName();echo '<br>';


            foreach ($users as $user) {
                $default = [
                    'driver' => env('DB_CONNECTION', 'mysql'),
                    'host' => env('DB_HOST'),
                    'port' => env('DB_PORT'),
                    'database' => $user->database_name,
                    'username' => 'root',
                    'password' => 'snow',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'prefix_indexes' => true,
                    'strict' => false,
                    'engine' => null
                ];

                \Config::set('database.connections.mysql', $default);
                DB::purge('mysql');

                $exists = DB::table('users')
                    ->where('unique_id', 6437100)
                    ->exists();


                //      if ($exists) {
                //          // Value exists in the current database connection
                //          echo "Value exists in $user->database_name.\n";
                //      } else {
                //          // Value doesn't exist in the current database connection
                //          echo "Value does not exist in $user->database_name.\n";
                //      }


                // $default = [
                //     'driver' => env('DB_CONNECTION', 'mysql'),
                //     'host' => env('DB_HOST'),
                //     'port' => env('DB_PORT'),
                //     'database' => $user->database_name,
                //     'username' => 'root',
                //     'password' => 'snow',
                //     'charset' => 'utf8mb4',
                //     'collation' => 'utf8mb4_unicode_ci',
                //     'prefix' => '',
                //     'prefix_indexes' => true,
                //     'strict' => false,
                //     'engine' => null
                // ];


                $valueToCheck = 'ShiftCare';

                $databaseConnections = ['ShiftCare_hock', 'ShiftCare_jasika'];  // Add more connections as needed

                foreach ($databaseConnections as $connectionName) {
                    $exists = DB::connection($connectionName)
                        ->table('users')  // Replace with your actual table name
                        ->where('unique_id', $valueToCheck)
                        ->exists();

                    if ($exists) {
                        echo "Value exists in $connectionName.\n";
                    } else {
                        echo "Value does not exist in $connectionName.\n";
                    }
                }


                echo $dbname = DB::connection()->getDatabaseName();
                echo '<br>';
            }
        } else {
            return view('auth.staff');
        }
    }

    public function sendOtp(Request $request)
    {
        $user = User::where(['email' => $request->email])->first();
        if (!$user) {
            return redirect()->back()->withError('Please Enter Valid Email.');
        }



        $otp = rand(1000, 9999);

        $url = url('/admin') . '/otp?token=' . base64_encode($request->email);
        //echo  $url;die;
        $this->data['detais'] = array('email' => $request->email, 'url' => $url);
        $email = $request->email;


        \Session::put('passwordTime', date('Y-m-d H:i:s'));

        //return view('email.otp',$this->data);
        // dd($data);

        User::updateOrInsert(
            ['email' => $request->email],
            ['otp' => $otp]
        );



        \Mail::send('email.otp', $this->data, function ($message) use ($email) {
            $message->to($email)->from('info@gmail.com')->subject('Password Reset OTP Code');
        });

        return redirect()->back()->withSuccess('OTP sent on your email please check.');



        //return redirect('admin/otp?token='.base64_encode($request->email))->withSuccess('OTP sent on your email please check.');
    }

    public function otp()
    {

        $resetTime = Session::get('passwordTime');


        $datetime_1 = $resetTime;
        $datetime_2 = date('Y-m-d H:i:s');

        $from_time = strtotime($datetime_1);
        $to_time = strtotime($datetime_2);
        $diff_minutes = round(abs($from_time - $to_time) / 60, 2);

        if ($diff_minutes > 5) {
            return redirect('/forgot-password')->withError('Your link expired plase try again.');
        }


        $this->data['email'] = base64_decode($_REQUEST['token']);
        return view('auth.passwords.otp', $this->data);
    }

    public function savePassword(Request $request)
    {
        //dd($request->all());
        $request->validate([

            'password' => 'required|min:6',
            'confirm_password' => 'required_with:password|same:password|min:6',
        ]);

        User::updateOrInsert(
            ['email' => $request->email],
            ['password' => \Hash::make($request->password)]
        );

        return redirect('/admin/success');
    }
}
