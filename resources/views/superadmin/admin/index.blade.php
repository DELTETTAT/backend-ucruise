@extends('layouts.vertical', ['title' => 'All Users'])

@section('css')
<!-- Plugins css -->
<link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />

@endsection

@section('content')
<?php error_reporting(0); ?>
<!-- Start Content-->
<div class="container-fluid">

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">List Users</li>
                    </ol>
                </div>
                <h4 class="page-title">List Users</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-12">

            <div class="card">
                <div class="card-body">

                    <a href="{{ url('admin/add-admin') }}" class="btn btn-defult" style="float: right;">Add Users</a>

                    <br>
                    <br>
                    <br>
                    <table class="table  table-design-default">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Database Name</th>
                                <th>Subscription</th>
                                <th>Status</th>
                                <th>Action</th>

                            </tr>
                        </thead>


                        <tbody>
                            @if(count($admins) > 0 )
                       
                            @foreach($admins as $key1=>$data)
                            <tr>
                                <td>{{$key1+1}}</td>
                                <td>{{$data->first_name.' '.$data->last_name}}</td>
                                <td>{{$data->email}}</td>
                                <td>{{$data->phone}}</td>
                                <td>{{@$data->database_name}}</td>
                                <td>
                                    @if($data->userSubscription->subscription->title)
                                    {{$data->userSubscription->subscription->title}}
                                    @else
                                    Demo
                                    @endif
                                </td>
                                <td>
                                    @if($data->status == 1)
                                    <button type="button" class="btn btn-success btn-xs waves-effect waves-light">Active</button>
                                    @else
                                    <button type="button" class="btn btn-danger btn-xs waves-effect waves-light">In-Active</button>
                                    @endif

                                </td>
                                <td>
                                    <a href="{{url('/admin/edit-admin',[$data->id])}}" class="btn btn-sm btn-info"><i class="fa fa-edit" style="cursor: pointer;"></i></a>
                                    <a href="{{url('/admin/delete-admin',[$data->id])}}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this?')"><i class="fas fa-trash" style="cursor: pointer;"></i></a>

                                    <a href="{{url('/admin/emptyDatabase',[$data->id])}}" class="btn btn-sm btn-info" onclick="return confirm('Are you sure want to empty {{@$data->database_name}} database?')" data-bs-toggle="tooltip"
   data-bs-placement="top"
   title="Empty {{ @$data->database_name }} database" ><i class="far fa-trash-alt" style="cursor: pointer;"></i></a>

   <a href="{{url('/admin/deleteAllDrivers',[$data->id])}}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure want to empty drivers {{@$data->database_name}} database?')" data-bs-toggle="tooltip" data-bs-placement="top" title="Empty drivers from {{ @$data->database_name }} database" ><i class="far fa-trash-alt" style="cursor: pointer;"></i></a>

                                </td>

                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="7" class="text-center">No data found</td>
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