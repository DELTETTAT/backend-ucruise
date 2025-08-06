@extends('layouts.vertical', ['title' => 'Subscriptions'])

@section('css')
<!-- Plugins css -->
<link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<style>
    /* Custom styles for card */
    .card.mb-4.shadow-sm.subscription-card {
        background-color: #FFFFFF !important;
        padding: 10px;
        height: 100%;
        /* Make sure the height is set to 100% */
        display: flex;
        flex-direction: column;
    }

    .subscription-card:hover {
        transform: scale(1.02);
    }

    .subscription-card .card-body {
        padding: 20px;
        flex: 1;
    }

    .subscription-card p {
        margin-bottom: 10px;
    }

    .active-card {
        border: 2px solid #007bff !important;
    }

    /* Hide yearly plans by default */
    .yearly-plan {
        display: none;
    }
</style>

@endsection

@section('content')
<?php error_reporting(0); ?>
<!-- Start Content-->
<div class="container-fluid">
    <div class="row mt-3">
        <div class="col-2">
            <!-- Your sidebar navigation -->
            <ul class="nav_list">
                <li>
                    <a href="{{url('users/invoice_settings')}}"><span>Invoice Settings</span></a>
                </li>
                <li>
                    <a href="{{route('prices.index')}}"><span>Prices</span></a>
                </li>
                <li>
                    <a href="{{route('award_group.index')}}"><span>Pay Groups</span></a>
                </li>
                <li>
                    <a href="{{route('allowance.index')}}"><span>Allowances</span></a>
                </li>

                <li>
                    <a href="{{url('users/reminders')}}"><span>Reminders</span></a>
                </li>

                <li class="activeli">
                    <a href="{{url('users/subscription')}}"><span>Subscription</span> </a>
                </li>
                <li>
                    <a href="{{route('billing.index')}}"><span>Billing</span> </a>
                </li>
                <li>
                    <a href="{{route('activity.index')}}"><span>Activity</span> </a>
                </li>
            </ul>
        </div>
        <div class="col-10">
            <div class="row mb-2">
                <div class="col-9">
                    <h3>Select your plan</h3>
                </div>
                <div class="col-3">
                    <!-- Toggle buttons for monthly and yearly plans -->
                    <div class="btn-group" role="group" aria-label="Subscription Toggle">
                        <button type="button" class="btn btn-primary toggle-btn" data-toggle="monthly">Monthly Plans</button>
                        <button type="button" class="btn btn-primary toggle-btn" data-toggle="yearly">Yearly Plans</button>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <!-- Monthly Plans -->
                        <div class="col-md-12">
                            <div class="row">
                                <!-- Loop through monthly plans and display -->
                                @foreach($monthlySubscriptions as $subscription)
                                <div class="col-md-4 monthly-plan">
                                    <div class="card mb-4 shadow-sm subscription-card @if($subscription->id==$currentSubscription_id)active-card @endif">
                                        <h4 class="card-title">{{ $subscription->title }} </h4>

                                        <h6 class="card-text">Price: {{ $subscription->price }}</h6>
                                        <h6 class="card-text">Description:{{$subscription->description}}</h6>
                                        <h6 class="card-text">Feature:</h6>
                                        <ul>
                                            @foreach($subscription->features as $feature)
                                            <li>{{$feature->name}}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <!-- Yearly Plans -->
                        <div class="col-md-12">
                            <div class="row">
                                <!-- Loop through yearly plans and display -->
                                @foreach($yearlySubscriptions as $subscription)
                                <div class="col-md-4 yearly-plan">
                                    <div class="card mb-4 shadow-sm subscription-card @if($subscription->id==$currentSubscription_id)active-card @endif">
                                        <h4 class="card-title">{{ $subscription->title }} </h4>

                                        <h6 class="card-text">Price: {{ $subscription->price }}</h6>
                                        <h6 class="card-text">Description:{{$subscription->description}}</h6>
                                        <h6 class="card-text">Feature:</h6>
                                        <ul>
                                            @foreach($subscription->features as $feature)
                                            <li>{{$feature->name}}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<!-- Your scripts here -->
<script>
    
        $(document).ready(function() {
        // Show monthly plans when Monthly button is clicked
        $('[data-toggle="monthly"]').click(function() {
            $('.monthly-plan').show();
            $('.yearly-plan').hide();
        });

        // Show yearly plans when Yearly button is clicked
        $('[data-toggle="yearly"]').click(function() {
            $('.yearly-plan').show();
            $('.monthly-plan').hide();
        });

        // Show monthly plans by default
        $('.monthly-plan').show();
        $('.yearly-plan').hide();
    });
 
</script>
@endsection
