<?php

namespace App\Http\Controllers\Superadmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\{Paygroup, Paygroupdata};
use Illuminate\Support\Facades\{Validator, Session};

class PaygroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['paygroup'] = Paygroup::orderBy('id','DESC')->get();
        return view("paygroup.index", $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $store = new Paygroupdata();
        $store->paygroup_id = $request->paygroup_id;
        $store->day_of_week = $request->day_of_week;
        $store->start_time = $request->start_time;
        $store->end_time = $request->end_time;
        $store->effective_date = $request->effective_date;
        $store->Xero_pay_item = $request->Xero_pay_item;
        $store->save();
        Session::flash("message", "You have successfully addedd.");
        return redirect()->route('award_group.index');
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
        //
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
        $store = Paygroupdata::where('id',$id)->first();
        //$store->paygroup_id = $request->paygroup_id;
        $store->day_of_week = $request->day_of_week;
        $store->start_time = $request->start_time;
        $store->end_time = $request->end_time;
        $store->effective_date = $request->effective_date;
        $store->Xero_pay_item = $request->Xero_pay_item;
        $store->save();
        Session::flash("message", "You have successfully updated.");
        return redirect()->route('award_group.index');
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

    public function payGroupStore(Request $request)
    {   

        $validator = Validator::make($request->all(), [
            "name" => "required|unique:paygroups"
        ]);

         

        if ($validator->fails()) {
            
            $errors = $validator->errors();

            //echo '<pre>';print_r($errors);die;
            Session::flash("error", 'Group name already exists.');
            return redirect()->back();
   
        }

        $store = new Paygroup();
        $store->name = $request->name;
        $store->save();
        Session::flash("message", "You have successfully added.");
        return redirect()->route('award_group.index');
    }

    public function payGroupUpdate(Request $request,$id)
    {   

        $validator = Validator::make($request->all(), [
            "name" => "required|unique:paygroups,name,".$id
        ]);

         

        if ($validator->fails()) {
            
            $errors = $validator->errors();

            //echo '<pre>';print_r($errors);die;
            Session::flash("error", 'Group name already exists.');
            return redirect()->back();
   
        }

        $store = Paygroup::where('id',$id)->first();
        $store->name = $request->name;
        $store->save();
        Session::flash("message", "You have successfully updated.");
        return redirect()->route('award_group.index');
    }
}
