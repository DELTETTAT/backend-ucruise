<?php

namespace App\Http\Controllers\Admin;

use App\Models\Reason;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class ReasonController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeLeaveReason(Request $request)
    {
        try {
            $reason = new Reason();
            $reason->message = $request->message;
            $reason->type = 0;
            $reason->save();

            Session::flash("message", "You have successfully added.");
            return redirect()->back();
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
    public function storeCancelRideReason(Request $request)
    {
        try {
            $reason = new Reason();
            $reason->message = $request->message;
            $reason->type = 3;
            $reason->save();
            

            Session::flash("message", "You have successfully added.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }

    }
    public function storeComplaintReason(Request $request)
    {
        try {
            $reason = new Reason();
            $reason->message = $request->message;
            $reason->type = 1;
            $reason->save();

            Session::flash("message", "You have successfully added.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }

    }
    public function storeRatingReason(Request $request)
    {
        try {
            $reason = new Reason();
            $reason->message = $request->message;
            $reason->type = 4;
            $reason->save();

            Session::flash("message", "You have successfully added.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }

    }

    public function storeTempReason(Request $request)
    {
        try {
            $reason = new Reason();
            $reason->message = $request->message;
            $reason->type = 5;
            $reason->save();

            Session::flash("message", "You have successfully added.");
            return redirect()->back();
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
    public function storeShiftChangeReason(Request $request)
    {
        try {
            $reason = new Reason();
            $reason->message = $request->message;
            $reason->type = 2;
            $reason->save();

            Session::flash("message", "You have successfully added.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Reason  $reason
     * @return \Illuminate\Http\Response
     */
    public function deleteReason($id)
    {
        try {
            $reason = Reason::find($id);
            if ($reason) {
                $reason->delete();
            }
            Session::flash("warning", "You have successfully deleted.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }
    public function deleteCancelRideReason($id)
    {
        try {
            $reason = Reason::find($id);
            if ($reason) {
                $reason->delete();
            }
            Session::flash("warning", "You have successfully deleted.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }
}
