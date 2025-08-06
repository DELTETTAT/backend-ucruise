<?php

namespace App\Http\Controllers\Superadmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Models\{Tax, Reminder, InvoiceSettings, Subscription, User, UserSubscription};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['edit'] = InvoiceSettings::first();
        $this->data['tax'] = Tax::first();
        return view("invoicesettings.add", $this->data);
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
    public function storeTax(Request $request, $id = 1)
    {
        Tax::updateOrInsert(['id' => $id], [
            'name' => $request->name,
            'tax' => $request->tax
        ]);

        Session::flash("success", "You have successfully updated.");
        return redirect()->route("invoice_settings");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function invoiceUpdate(Request $request)
    {

        InvoiceSettings::updateOrInsert(['id' => 1], [
            'abn' => $request->abn,
            'address' => $request->address,
            'phone' => $request->phone,
            'payment_return' => $request->payment_return,
            'contact_email' => $request->contact_email,
            'email_message' => $request->email_message,
            'payment_rounding' => $request->payment_rounding,
            'provider_number' => $request->provider_number,
            'cost_calcculation' => $request->cost_calcculation,
            'cancelled_by_client' => $request->cancelled_by_client,
            'client_message' => $request->client_message,
            'invoice_item_default_format' => $request->invoice_item_default_format
        ]);

        Session::flash("success", "You have successfully updated.");
        return redirect()->route("invoice_settings");
    }


    // List Reminders
    public function reminders(Request $request, $id = 1)
    {
        $this->data['reminders'] = Reminder::get();
        return view("reminder.index", $this->data);
    }
    // Add reminders
    public function addReminder(Request $request, $id = 1)
    {
        return view("reminder.add");
    }

    public function reminderStore(Request $request)
    {
        $reminder = new Reminder();
        $reminder->target = $request->target;
        $reminder->date = $request->date;
        $reminder->content = $request->content;
        $reminder->description = $request->description;
        $reminder->save();
        Session::flash("success", "You have successfully added.");
        return redirect()->route("reminders");
    }

    // Edit Reminder
    public function editReminder($id)
    {
        $this->data['edit'] = Reminder::where('id', $id)->first();
        return view("reminder.edit", $this->data);
    }

    // Reminder Update reminderUpdate

    public function reminderUpdate(Request $request, $id)
    {
        $reminder = Reminder::where('id', $id)->first();
        $reminder->target = $request->target;
        $reminder->date = $request->date;
        $reminder->content = $request->content;
        $reminder->description = $request->description;
        $reminder->save();
        Session::flash("success", "You have successfully updated.");
        return redirect()->route("reminders");
    }

    // subscription
    public function subscription()
    {
        $user= Auth::guard('staff')->user();
        if(!$user){
        $temp_DB_name = DB::connection()->getDatabaseName();
        $default_DBName = env("DB_DATABASE");
        $this->connectDB($default_DBName);
        $this->data['monthlySubscriptions'] = Subscription::with('features')->where('billing_cycle','monthly')->get();
        $this->data['yearlySubscriptions'] = Subscription::with('features')->where('billing_cycle','yearly')->get();
        $subscriptions_id = Subscription::get()->pluck('id');
        $this->data['currentSubscription_id'] = UserSubscription::where('user_id', Auth::user()->id)->whereIn('subscription_id', $subscriptions_id)->where('end_date', '>', now())->pluck('subscription_id')->first();
        $this->connectDB($temp_DB_name);
        }

        return view("subscription.index", $this->data);
    }
}




