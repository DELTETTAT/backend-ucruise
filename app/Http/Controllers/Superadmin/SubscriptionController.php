<?php

namespace App\Http\Controllers\Superadmin;

use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Support\Facades\Session;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $this->data['subscriptions'] =  Subscription::with('features')->orderBy('id', 'DESC')->get();

        return view('superadmin.subscription.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {


        $this->data['features'] =  Feature::get();
        return view('superadmin.subscription.add',  $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'feature' => 'required'
        ]);


        $store = new Subscription;
        $store->title = $request->title;
        $store->price = $request->price;
        $store->description = $request->description;
        $store->billing_cycle = $request->billing_cycle;
        $store->status = $request->status;
        $store->save();

        if ($request->has('feature')) {
            $store->features()->sync($request->feature);
        } else {
            // If no features selected, detach all existing features
            $store->features()->detach();
        }

        Session::flash('message', 'You have successfully added.');
        return redirect()->route('subscription.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $subscription = Subscription::findOrFail($id);
        $subscription->features()->sync([]);
        // Delete the subscription
        $subscription->delete();

        Session::flash('warning', 'You have successfully deleted.');
        return redirect()->route('subscription.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->data['edit'] = Subscription::with('features')->where('id', $id)->first();
        $this->data['features'] = Feature::all();


        return view('superadmin.subscription.edit', $this->data);
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
        $store = Subscription::where('id', $id)->first();
        $store->title = $request->title;
        $store->price = $request->price;
        $store->description = $request->description;
        $store->status = $request->status;
        $store->save();


        if ($request->has('feature')) {
            $store->features()->sync($request->feature);
        } else {
            // If no features selected, detach all existing features
            $store->features()->detach();
        }
        Session::flash('message', 'You have successfully updated.');
        return redirect()->route('subscription.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $subscription = Subscription::findOrFail($id);
        // Detach features
        $subscription->features()->sync();
        // Delete the subscription
        $subscription->delete();


        Session::flash('warning', 'You have successfully deleted.');
        return redirect()->route('subscription.index');
    }
}
