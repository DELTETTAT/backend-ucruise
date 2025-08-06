<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{Teams, User};
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['teams'] = Teams::get();
        return view("teams.index", $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try {
            $this->data["staff"] = User::whereHas("roles", function ($q) {
                $q->whereIn("name", ["admin", 'carer', 'hr', 'staff']);
            })
                ->orderBy("id", "DESC")
                ->get();
            return view("teams.add", $this->data);
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
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
            $team = new Teams();
            $team->name = $request->name;
            $team->staff = implode(',', $request->staff);
            $team->save();
            Session::flash("message", "You have successfully addedd.");
            return redirect()->route('teams');
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
            $this->data["staff"] = User::whereHas("roles", function ($q) {
                $q->whereIn("name", ["admin", 'carer', 'hr']);
            })
                ->orderBy("id", "DESC")
                ->get();

            $this->data['edit'] = Teams::where('id', $id)->first();
            return view("teams.edit", $this->data);
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
            $team = Teams::where('id', $id)->first();
            $team->name = $request->name;
            $team->staff = "";
            $team->staff = implode(',', $request->staff);
            $team->save();
            Session::flash("message", "You have successfully updated.");
            return redirect()->route('teams');
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }
    
}
