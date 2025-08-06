<?php

namespace App\Http\Controllers\Superadmin;

use App\Models\Allowances;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{DB, Session};

class AllowanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['allowance'] = Allowances::orderBy('id','DESC')->get();
        return view("allowance.index", $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        $this->data['type'] = DB::table('allowance_type')->get();
        return view("allowance.add", $this->data);
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
            $store = new Allowances();
            $store->name = $request->name;
            $store->type = $request->type;
            $store->value = $request->value;
            $store->xero_pay_item = $request->xero_pay_item;
            $store->save();
    
            Session::flash("message", "You have successfully added.");
            return redirect()->route("allowance.index");
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
        $this->data['type'] = DB::table('allowance_type')->get();
        $this->data["edit"] = Allowances::where("id",$id)->first();
        return view("allowance.edit", $this->data);
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
            $update = Allowances::where("id",$id)->first();
            $update->name = $request->name;
            $update->type = $request->type;
            $update->value = $request->value;
            $update->xero_pay_item = $request->xero_pay_item;
            $update->save();
    
            Session::flash("message", "You have successfully added.");
            return redirect()->route("allowance.index");
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
