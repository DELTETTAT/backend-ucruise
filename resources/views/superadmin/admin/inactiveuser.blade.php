@extends('layouts.vertical', ['title' => 'Inactive Users'])

@section('css')
<!-- Plugins css -->
<link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />

@endsection

@section('content')
<style>
    .btn-success {
        box-shadow: 0 0 0 0.15rem rgb(60 198 171 / 50%);
    }
</style>
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
                        <li class="breadcrumb-item active">Inactive Users</li>
                    </ol>
                </div>
                <h4 class="page-title">Close Account</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-12">
            @if(Session::has('message'))
            <p class="alert alert-success">{{ Session::get('message') }}</p>
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

            @if ($message = Session::get('warning'))
            <div class="alert alert-danger alert-block">
                <button type="button" class="close" data-dismiss="alert">X</button>
                <strong>{{ $message }}</strong>
            </div>
            @endif


            <div class="card">
                <div class="card-body">

                    <!-- <a href="{{ url('admin/add-admin') }}" class="btn btn-success" style="float: right;">Add Users</a> -->
                    <table class="table table-design-default">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Database Name</th>
                                <th>Account Status</th>
                                <th>Action</th>

                            </tr>
                        </thead>


                        <tbody>
                            @if(count($inactiveUser) > 0 )
                            @foreach($inactiveUser as $key1=>$data)
                            <tr>
                                <td>{{$key1+1}}</td>
                                <td>{{$data->first_name.' '.$data->last_name}}</td>
                                <td>{{$data->email}}</td>
                                <td>{{$data->phone}}</td>
                                <td>{{@$data->database_name}}</td>

                                <td>
                                    @if($data->close_account == 1)
                                    <button type="button" class="btn btn-success btn-xs waves-effect waves-light">Active</button>
                                    @else
                                    <button type="button" class="btn btn-danger btn-xs waves-effect waves-light">In-Active</button>
                                    @endif

                                </td>
                                <td>
                                    <a href="{{route('activateAccount',[$data->id])}}"><button type="button" class="btn btn-success btn-xs waves-effect waves-light">Activate Account</button></a>
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