<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
  public function index()
  {
    return view('vehicles.index');
  }

  public function add()
  {
    return view('vehicles.add');
  }
  public function show()
  {
    $vehicles = Vehicle::all();

    return view('vehicles.show', compact('vehicles'));
  }
  public function store(Request $request)
  {

    $validator = Validator::make($request->all(), [
      "name" => "required",
      "description" => "required",
      "seats" => "required|numeric",
      "fare" => "required|numeric",

    ]);


    if ($validator->fails()) {

      $errors = $validator->errors();
      Session::flash("error", $errors);
      return redirect()->back();
    }

    $store =  new Vehicle();
    $store->name = $request->name;
    $store->description = $request->description;
    $store->seats = $request->seats;
    $store->fare = $request->fare;

    $store->save();
    Session::flash("message", "You have successfully added vehicle.");
    return redirect()->back();
  }
  public function edit($id)
  {
    try {

      $this->data['edit'] = Vehicle::where('id', $id)->first();

      return view("vehicles.edit", $this->data);
    } catch (\Exception $ex) {
      $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
      Session::flash("error", $this->data["msg"]);
      return redirect()->back();
    }
  }
  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      "name" => "required",
      "description" => "required",
      "seats" => "required|numeric",
      "fare" => "required|numeric",
    ]);

    if ($validator->fails()) {
      $errors = $validator->errors();
      Session::flash("error", $errors);
      return redirect()->back();
    }
    try {
      // $results = DB::select(DB::raw("SELECT s.id from vehicles v, schedules s, schedule_carers sc where v.id = $id
      // AND
      // v.id = s.vehicle_id
      // AND
      // s.id = sc.schedule_id
      // AND 
      // (SELECT COUNT(*) FROM schedule_carers sct WHERE sct.schedule_id = s.id GROUP BY sct.schedule_id)>$request->seats"));
      $seats = $request->seats;
      $results = DB::table('vehicles as v')
        ->join('schedules as s', 'v.id', '=', 's.vehicle_id')
        ->join('schedule_carers as sc', 's.id', '=', 'sc.schedule_id')
        ->select('s.id')
        ->where('v.id', $id)
        ->where('s.id', '>', 0)
        ->where(function ($query) use ($seats) {
          $query->where(DB::raw('(SELECT COUNT(*) FROM schedule_carers sct WHERE sct.schedule_id = s.id GROUP BY sct.schedule_id)'), '>', $seats);
        })->get();
      //  dd($results);
      $update = Vehicle::where('id', $id)->first();
      $update->name = $request->name;
      $update->description = $request->description;

      if ($results->isEmpty()) {
        $update->seats = $request->seats;
        $update->fare = $request->fare;
        $update->update();
        Session::flash("message", "You have successfully updated.");
      } else {
        Session::flash("error", "Couldn't update");
      }
      return redirect()->route("vehicles.show");
    } catch (\Exception $ex) {
      $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
      Session::flash("error", $this->data["msg"]);
      return redirect()->back();
    }
  }
  public function destroy($id)
  {
    try {
      Vehicle::destroy($id);
      Session::flash("warning", "You have successfully deleted.");
      return redirect()->route("vehicles.show");
    } catch (\Exception $ex) {
      $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
      Session::flash("error", $this->data["msg"]);
      return redirect()->back();
    }
  }

  public function privacyPolicy()
  {
    return view('privacy-policy');
  }
}
