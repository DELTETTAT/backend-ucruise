<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SubUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;


class ApiAuth
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
            dd('user');
            $user = User::where('id', Auth::id())->whereHas('roles', function ($q) {
                $q->where('name', 'admin');
            })->first();

            if ($user) {
                $this->connectDB($user);
                Auth::loginUsingId(Auth::id());
            }

            return $next($request);
        } else if (Auth::guard('staff')->check()) {
            $staff = SubUser::where('id', Auth::guard('staff')->id())->first();

            if ($staff) {
                $this->connectDB($staff);
                Auth::guard('staff')->loginUsingId(Auth::guard('staff')->id());
            }

            return $next($request);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
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
