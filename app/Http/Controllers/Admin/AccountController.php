<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Models\{DocCategory, QualificationCategory, ReportHeadingCategory, Holiday, Notes, Scheduler, Timezone, Settings, ShiftTypes, TimeAttendence, Note_permission, CompanyDetails, Faq, Leave, PriceBook, Reason, ReportHeading, RideSetting, ScheduleTemplate, User};

class AccountController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['docCategories'] = DocCategory::orderBy('id', 'DESC')->get();
        $this->data['qualificationCategory'] = QualificationCategory::orderBy('id', 'DESC')->get();
        $this->data['reportHeadingCategory'] = ReportHeadingCategory::with('catHeadings')->orderBy('id', 'DESC')->get();
        $this->data['holiday'] = Holiday::orderBy('id', 'DESC')->get();
        $this->data['pNotes'] = Notes::where('category_name', 'Progress Notes')->orderBy('id', 'DESC')->get();
        $this->data['fNotes'] = Notes::where('category_name', 'Feedback')->orderBy('id', 'DESC')->get();
        $this->data['inc'] = Notes::where('category_name', 'Incident')->orderBy('id', 'DESC')->get();
        $this->data['enq'] = Notes::where('category_name', 'Enquiry')->orderBy('id', 'DESC')->get();
        $this->data['needInfo'] = Notes::where('category_name', 'Need to know information')->orderBy('id', 'DESC')->get();
        $this->data['useInfo'] = Notes::where('category_name', 'Useful information')->orderBy('id', 'DESC')->get();
        $this->data['cleintTypes'] = Scheduler::orderBy('id', 'DESC')->get();
        $this->data['timezones'] = Timezone::orderBy('id', 'DESC')->get();
        $this->data['shiftTypes'] = ShiftTypes::orderBy('id', 'DESC')->get();
        $this->data['leaveTypes'] = Leave::pluck('type');
        $this->data['settings'] = Settings::first();
        $this->data['tA'] = TimeAttendence::first();
        $this->data['nP'] = Note_permission::first();
        $this->data['rideSettings'] = RideSetting::first();
        $company = CompanyDetails::first();
        $companyDetails = \DB::table('company_addresses')
            ->where('company_addresses.company_id', $company->id)
            ->whereNull('company_addresses.end_date')
            ->join('company_details', 'company_addresses.company_id', '=', 'company_details.id')
            ->select('company_details.id', 'company_details.name', 'company_details.logo', 'company_details.email', 'company_details.phone', 'company_addresses.address', 'company_addresses.latitude', 'company_addresses.longitude')
            ->first();


        $this->data['companyDetails'] = $companyDetails ? $companyDetails : $company;
   
        $this->data['cancelRideReasons'] = Reason::where('type', 3)->get();
        $this->data['leaveReasons'] = Reason::where('type', 0)->get();
        $this->data['ratingReasons'] = Reason::where('type', 4)->get();
        $this->data['complaintReasons'] = Reason::where('type', 1)->get();
        $this->data['shiftChangeReasons'] = Reason::where('type', 2)->get();
        $this->data['tempChangeReasons'] = Reason::where('type', 5)->get();
        $this->data['faqs'] = Faq::get();
        $this->data['pricebooks'] = PriceBook::get();
        $this->data['scheduleTemplate'] = ScheduleTemplate::with('pricebook')->get();


        return view("staff.account.list", $this->data);
    }

    /**
     * Upload Document Category
     */
    public function uploadDocCategory(Request $request)
    {
        try {
            $doc = new DocCategory();
            $doc->category_name = $request->category_name;
            $doc->save();

            Session::flash("success", "You have successfully added.");
            return redirect('users/account#' . $request->redirect);
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Upload Qualification Category
     */
    public function uploadQualificationCategory(Request $request)
    {
        try {
            $doc = new QualificationCategory();
            $doc->category_name = $request->category_name;
            $doc->save();

            Session::flash("success", "You have successfully added.");
            return redirect('users/account#' . $request->redirect);
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Upload Report Heading
     */
    public function uploadReportHeading(Request $request)
    {
        try {
            $doc = new ReportHeadingCategory();
            $doc->category_name = $request->category_name;
            $doc->save();

            Session::flash("success", "You have successfully added.");
            return redirect('users/account#' . $request->redirect);
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Upload Report Headings
     */
    public function uploadReportHeadings(Request $request)
    {
        try {
            $doc = new ReportHeading();
            $doc->category_id = $request->category_id;
            $doc->name = $request->name;
            $doc->save();

            Session::flash("success", "You have successfully added.");
            return redirect('users/account#' . $request->redirect);
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Upload Public Holidays
     */
    public function uploadPublicHoliday(Request $request)
    {
        try {
            $doc = new Holiday();
            $doc->date = date('Y-m-d', strtotime($request->date));
            $doc->save();

            Session::flash("success", "You have successfully added.");
            return redirect('users/account#' . $request->redirect);
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Upload Shift Type
     */
    public function shiftTypeStore(Request $request)
    {
        try {
            $shiftType = new ShiftTypes();
            $shiftType->name = $request->name;
            $shiftType->color = $request->color;
            $shiftType->save();

            Session::flash("message", "You have successfully added.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }
    public function storeFaq(Request $request)
    {
        try {
            $faq = new Faq();
            $faq->question = $request->question;
            $faq->answer = $request->answer;
            $faq->save();
            Session::flash("message", "You have successfully added.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Upload Document Category
     */
    public function storeDocCategory(Request $request)
    {
        try {
            $docCategory = new DocCategory();
            $docCategory->category_name = $request->category_name;
            $docCategory->save();

            Session::flash("message", "You have successfully added.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Upload Report Headings
     */
    public function storeReportHeading(Request $request)
    {
        try {
            $reportHeading = new ReportHeading();
            $reportHeading->category_id  = $request->heading_id;
            $reportHeading->name  = $request->name;
            $reportHeading->save();

            Session::flash("message", "You have successfully added.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Upload Holidays
     */
    public function storeHoliday(Request $request)
    {
        try {
            $holiday = new Holiday();
            $holiday->date = $request->date;
            $holiday->name = $request->name;
            $holiday->description = $request->description;
            $holiday->save();

            Session::flash("success", "You have successfully added.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Delete Note
     */
    public function deleteNote($id)
    {
        try {
            $note = Notes::find($id);
            if ($note) {
                $note->delete();
            }
            Session::flash("warning", "You have successfully deleted.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }
    public function deleteFaq($id)
    {
        try {
            $faq = Faq::find($id);
            if ($faq) {
                $faq->delete();
            }
            Session::flash("warning", "You have successfully deleted.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Delete Shift Type
     */
    public function deleteShiftType($id)
    {
        try {
            $shiftType = ShiftTypes::find($id);
            if ($shiftType) {
                $shiftType->delete();
            }
            Session::flash("warning", "You have successfully deleted.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Delete Document Category
     */
    public function deleteDocCategory($id)
    {
        try {
            $doc_category = DocCategory::find($id);
            if ($doc_category) {
                $doc_category->delete();
            }
            Session::flash("warning", "You have successfully deleted.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Delete Client Type
     */
    public function deleteClientType($id)
    {
        try {
            $client_type = Scheduler::find($id);
            if ($client_type) {
                $client_type->delete();
            }
            Session::flash("warning", "You have successfully deleted.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Delete Holidays
     */
    public function deleteHoliday($id)
    {
        try {
            $holiday = Holiday::find($id);
            if ($holiday) {
                $holiday->delete();
            }
            Session::flash("warning", "You have successfully deleted.");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    /**
     * Delete Report Headings
     */
    public function deleteReportHeading($id)
    {
        try {
            $reportHeading = ReportHeading::find($id);
            if ($reportHeading) {
                $reportHeading->delete();
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
