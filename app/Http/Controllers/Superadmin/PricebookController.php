<?php

namespace App\Http\Controllers\Superadmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\{PriceBook, PriceTableData};
use Illuminate\Support\Facades\{Validator, Session};

class PricebookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        // $this->data['pricebook'] = PriceBook::orderBy('id','DESC')->get();
        $pricebooks = PriceBook::orderBy('id', 'DESC')->with('priceBookData')->get();
        return view("price.index")->with('pricebooks', $pricebooks);
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
        $store = new PriceTableData();
        $store->price_book_id = $request->price_book_id;
        $store->day_of_week = $request->day_of_week;
        $store->start_time = $request->start_time;
        $store->end_time = $request->end_time;
        $store->per_hour = $request->per_hour;
        $store->refrence_no_hr = $request->refrence_no_hr;
        $store->per_km = $request->per_km;
        $store->refrence_no = $request->refrence_no;
        $store->effective_date = $request->effective_date;
        $store->per_ride = $request->per_ride;
        $store->multiplier = $request->multiplier;
        $store->save();
        Session::flash("message", "You have successfully addedd.");
        return redirect()->route('prices.index');
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
     * Edit the specified price
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $priceTableData = PriceTableData::find($id);
        if ($priceTableData) {
            return response()->json(['success' => true, 'data' => $priceTableData], 200);
        } else {
            return response()->json(['error' => true, 'message' => 'Price Book not found'], 400);
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function pricebookEdit($id)
    {
        $priceBook = PriceBook::find($id);
        if ($priceBook) {
            return response()->json(['success' => true, 'data' => $priceBook], 200);
        } else {
            return response()->json(['error' => true, 'message' => 'Price Book not found'], 400);
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
        $update = PriceTableData::where('id', $id)->first();
        //$update->price_book_id = $request->price_book_id;
        $update->day_of_week = $request->day_of_week;
        $update->start_time = $request->start_time;
        $update->end_time = $request->end_time;
        $update->per_hour = $request->per_hour;
        $update->refrence_no_hr = $request->refrence_no_hr;
        $update->per_km = $request->per_km;
        $update->refrence_no = $request->refrence_no;
        $update->effective_date = $request->effective_date;
        $update->per_ride = $request->per_ride;
        $update->multiplier = $request->multiplier;
        $update->save();
        Session::flash("message", "You have successfully updated.");
        return redirect()->route('prices.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function pricebookStore(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "name" => "required|unique:price_books"
        ]);



        if ($validator->fails()) {

            $errors = $validator->errors();

            Session::flash("error", 'Price book name already exists.');
            return redirect()->back();
        }

        $store = new PriceBook();
        $store->name = $request->name;
        $store->external_id = $request->external_id;
        $store->fixed_price = 0;
        $store->provider_travel = 0;
        if (isset($request->fixed_price)) {
            $store->fixed_price = 1;
        }

        if (isset($request->provider_travel)) {
            $store->provider_travel = 1;
        }


        $store->save();
        Session::flash("message", "You have successfully added.");
        return redirect()->route('prices.index');
    }


    public function priceBookUpdate(Request $request)
    {



        $validator = Validator::make($request->all(), [
            "name" => "required|unique:price_books,name," . $request->id

        ]);





        if ($validator->fails()) {

            $errors = $validator->errors();

            Session::flash("error", 'Price book name already exists.');
            return redirect()->back();
        }

        $store = PriceBook::where('id', $request->id)->first();
        $store->name = $request->name;
        $store->external_id = $request->external_id;
        $store->fixed_price = isset($request->fixed_price) ? 1 : 0;
        $store->provider_travel = isset($request->provider_travel) ? 1 : 0;

        $store->save();
        Session::flash("message", "You have successfully updated.");
        return redirect()->route('prices.index');
    }
}
