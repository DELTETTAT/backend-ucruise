@extends('layouts.vertical', ['title' => 'Subscription'])

@section('css')
    <!-- Plugins css -->
    <link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />

@endsection

@section('content')
 <?php error_reporting(0); ?>
    <!-- Start Content-->
    <div class="container-fluid">
        
        <!-- start page title -->
        <!-- <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Subscription</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Subscription</h4>
                </div>
            </div>
        </div>      -->
        <!-- end page title --> 

        <div class="row mt-3">
        <div class="col-2">
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
         </ul>
      </div>
            <div class="col-10">
                

                <div class="card">
                    <div class="card-body">
                    <div class="col-md-12"><h4>Close Your ShiftCare Account</h4></div>
                    <div class="col-md-12">
   <div class="box">
      <div class="box-header with-border"><i style="color:red">Please only close your site if you are sure that you no longer wish to use Shiftcare.</i></div>
      <div class="box-body"><a  onclick="return confirm('Are you sure you want to close account?')"  class="btn btn-danger btn-flat"   href="{{route('closeAccount')}}" id="bb-cancel"  >Close Account</a></div>
   </div>
</div>

                    </div> <!-- end card body-->
                </div> <!-- end card -->
            </div><!-- end col-->
        </div>
        <!-- end row-->

   
        
    </div> <!-- container -->
@endsection

@section('script')
    <!-- Plugins js-->
    <script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
    <script src="{{asset('assets/libs/pdfmake/pdfmake.min.js')}}"></script>

    <!-- Page js-->
    <script src="{{asset('assets/js/pages/datatables.init.js')}}"></script>
@endsection