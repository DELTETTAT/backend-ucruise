@extends('layouts.vertical', ['title' => 'All Announcement'])

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
                        <li class="breadcrumb-item active">List Announcement</li>
                    </ol>
                </div>
                <h4 class="page-title">List Announcement</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-12">



            <div class="card">
                <div class="card-body">

                    <a href="{{url('admin/send-announcement')}}" class="btn btn-defult" style="float: right;">Send Announcement</a>
                    <table class="table table-design-default">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Action</th>

                            </tr>
                        </thead>


                        <tbody>
                            @if(count($announces) > 0 )
                            @foreach($announces as $key1=>$data)
                            <tr>
                                <td>{{$key1+1}}</td>
                                <td>{{$data->title}}</td>
                                <td>{{$data->message}}</td>

                                <td>
                                    <a href="{{url('/admin/delete-announcement',[$data->id])}}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this?')"><i class="fas fa-trash" style="cursor: pointer;"></i></a>
                                </td>

                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="4" class="text-center">No data found</td>
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