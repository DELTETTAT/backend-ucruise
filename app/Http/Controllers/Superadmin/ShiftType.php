<?php

namespace App\Http\Controllers\Superadmin;

use App\Models\ShiftTypes;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class ShiftType extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        $this->data["shift"] = ShiftTypes::orderBy("id", "DESC")
                ->get();
        return view("shifttype.index", $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("shifttype.add");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        
        try{
        $store = new ShiftTypes();
        $store->name = $request->name;
        $store->external_id = $request->external_id;
        $store->color = $request->color;
        $store->save();

        Session::flash("message", "You have successfully added.");
        return redirect()->route("shift-type.index");
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            return view("pages.500", $this->data);
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
        $this->data["edit"] = ShiftTypes::where("id",$id)->first();
        return view("shifttype.edit", $this->data);
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
        try{
            $update = ShiftTypes::where("id",$id)->first();
            $update->name = $request->name;
            $update->external_id = $request->external_id;
            $update->color = $request->color;
            $update->save();
    
            Session::flash("message", "You have successfully updated.");
            return redirect()->route("shift-type.index");
            } catch (\Exception $ex) {
                $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
                return view("pages.500", $this->data);
            }
    }

    public function del(Request $request, $id)
    {
        try{
            $update = ShiftTypes::where("id",$id)->first();
            $update->name = $request->name;
            $update->external_id = $request->external_id;
            $update->color = $request->color;
            $update->save();
    
            Session::flash("message", "You have successfully updated.");
            return redirect()->route("shift-type.index");
            } catch (\Exception $ex) {
                $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
                return view("pages.500", $this->data);
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
}
