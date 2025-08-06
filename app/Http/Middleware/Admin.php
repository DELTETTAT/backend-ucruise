<?php

namespace App\Http\Middleware;

use App\Models\SubUser;
use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {

            // only if role is admin then will connected own database
            $user = User::where('id', Auth::Id())->whereHas('roles', function ($q) {
                $q->where('name', 'admin');
            })->first();


            if ($user) {
                $this->connectDB($user);

                Auth::loginUsingId(Auth::Id());
            }

            // $path = \Request::route()->getName();
            // $user = User::where('id',Auth::Id())->first();

            //dd($user->role());

            // echo $path;

            // if($path == 'staff.list'){

            // }else{
            //     abort(403, 'You do not have permission.');
            // }



            return $next($request);
        } else if (Auth::guard('staff')->check()) {

            // only if role is staff then will connected own database
            $staff = SubUser::where('id', Auth::guard('staff')->Id())->first();


            if ($staff) {
                $this->connectDB($staff);

                Auth::guard('staff')->loginUsingId(Auth::guard('staff')->Id());
            }

            // $path = \Request::route()->getName();
            // $user = User::where('id',Auth::Id())->first();

            //dd($user->role());

            // echo $path;

            // if($path == 'staff.list'){

            // }else{
            //     abort(403, 'You do not have permission.');
            // }



            return $next($request);
        } else if (auth('sanctum')->check()) {
            $this->connectDB(auth('sanctum')->user());
            return $next($request);
        } else {

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => 'false', 'message' => 'Unauthorised User'], 401);
            } else {

                return redirect()->route('login');
            }
        }
    }

    public function connectDB($user)
    {
        $default = [
            'driver' => env('DB_CONNECTION', 'mysql'),
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => $user->database_name,
            'username' => $user->database_username,
            'password' => $user->database_password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null
        ];

        Config::set('database.connections.mysql', $default);
        DB::purge('mysql');
    }
}
