<?php

namespace App\Http\Controllers;

use App\Models\SubUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function changePassword(Request $request)
    {
        $findSubUser = SubUser::where('email', $request->email)->first();
        if (isset($findSubUser)) {
            $findSubUser->password = Hash::make($request->password);
            $findSubUser->save();
            return response()->json(['success' => true, "message" => 'Done'], 200);
        }
        return response()->json(['success' => false, "message" => 'Something went wrong'], 500);
    }

    
}
