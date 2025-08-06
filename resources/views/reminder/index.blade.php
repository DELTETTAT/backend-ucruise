@extends('layouts.vertical', ['title' => 'List Reminders'])

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
                            <li class="breadcrumb-item active">List Reminders</li>
                        </ol>
                    </div>
                    <h4 class="page-title">  Lists Reminders</h4>
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

            <li class="activeli">
               <a href="{{url('users/reminders')}}"><span>Reminders</span></a>
            </li>

            <li>
               <a href="{{url('users/subscription')}}"><span>Subscription</span> </a>
            </li>
            <li>
                <a href="{{route('billing.index')}}"><span>Billing</span> </a>
             </li>
             <li>
                <a href="{{url('users/subscription')}}"><span>Activity</span> </a>
             </li>
         </ul>
      </div>
            <div class="col-10">
            @if ($message = Session::get('warning'))  
                <p class="alert alert-warning">{{ $message }}</p>
                @endif

                @if ($errors->any())
                <div class="alert alert-danger">
                <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
                </ul>
                </div>
                @endif

            @if ($message = Session::get('success'))  
            <div class="alert alert-success alert-block">  
            <button type="button" class="close" data-dismiss="alert">X</button>   
            <strong>{{ $message }}</strong>  
            </div>  
            @endif  


                <div class="card">
                    <div class="card-body">
                     
                    <a href="{{ route('add.reminder') }}" class="btn btn-defult" style="float: right;">Add Reminder</a>
                     
                    <br>
                    <br>
                    <br>
                        <table class="table table-design-default">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Target</th>
                                    <th>Title</th>
                                    <th>Action</th>
                                    
                                     
                                </tr>
                            </thead>
                        
                        
                            <tbody>
                            @if(count($reminders) > 0 )
                            @foreach($reminders as $key1=>$data)
                            <?php
                               // echo '<pre>';print_r($data->roles[0]->name);
                            ?>
                                <tr>
                                    <td>{{$key1+1}}</td>
                                    <td>{{$data->date}}</td>
                                    <td>{{$data->target}}</td>
                                    <td>{{$data->content}}</td>
                                    
                                    
                                    <td style="display:none1">
                             <a href="{{url('/users/edit-reminder',[$data->id])}}"><i class="fa fa-edit text-warning mr-2" style="cursor: pointer;"></i></a>
                    <a href="<?php echo url('/users/deleteCategory');?>/{{$data->id}}/{{$data->getTable()}}" onclick="return confirm('Are you sure you want to delete this?')"><i class="fas fa-trash text-danger" style="cursor: pointer;"></i></a>
                                          
                                    </td>
                                     
                                </tr>
                            @endforeach  
                            @else
                            <tr>
                                <td colspan="5" class="text-center">No data found</td>
                            </tr>
                            @endif 
                            </tbody>
                        </table>

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