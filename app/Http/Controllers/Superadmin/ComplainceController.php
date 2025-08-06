<?php

namespace App\Http\Controllers\Superadmin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\{StaffDocument, ReportHeadingCategory};

class ComplainceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $compliance = ReportHeadingCategory::with('catHeadings')->whereRaw("LOWER(category_name) = 'compliance'")->first();
        $this->data['doc'] = StaffDocument::select(DB::raw('group_concat(category) as names'),'staff_id',DB::raw('group_concat(expire) as expire'))->groupBy('staff_id')->get();

        $this->data['AllUserDoc'] = StaffDocument::select(DB::raw('group_concat(category) as names'),'staff_id',DB::raw('group_concat(expire) as expire'))->groupBy('staff_id')->get();

        // $this->data["admins"] = User::whereHas("roles", function ($q) {
        //     $q->where("name", "user");
        // })
        //     ->where('close_account',1)
        //     ->orderBy("id", "DESC")
        //     ->get();

        //return $this->data['doc'];
        //echo '<pre>';print_r($this->data['compliance']->catHeadings);die;
        $this->data['compliance'] = @$compliance->catHeadings;

        // dd($this->data);
        return view("report.compliance.index", $this->data);
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
        //
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
        //
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


    public function index_bck()
    {
        $compliance = ReportHeadingCategory::with('catHeadings')->whereRaw("LOWER(category_name) = 'compliance'")->first();
        $this->data['doc'] = StaffDocument::select(DB::raw('group_concat(category) as names'),'staff_id',DB::raw('group_concat(expire) as expire'))->groupBy('staff_id')->get();

        // $this->data["admins"] = User::whereHas("roles", function ($q) {
        //     $q->where("name", "user");
        // })
        //     ->where('close_account',1)
        //     ->orderBy("id", "DESC")
        //     ->get();

        //return $this->data['doc'];
        //echo '<pre>';print_r($this->data['compliance']->catHeadings);die;
        $this->data['compliance'] = $compliance->catHeadings;
        return view("report.compliance.index", $this->data);
    }
}
