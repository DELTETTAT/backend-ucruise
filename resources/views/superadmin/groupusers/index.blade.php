@extends('layouts.vertical', ['title' => 'Group Users'])

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
                        <li class="breadcrumb-item active">List Group Users</li>
                    </ol>
                </div>
                <h4 class="page-title">List Group Users</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-12">



            <div class="card">
                <div class="card-body">

                    <a href="{{url('admin/create-group')}}" class="btn btn-defult" style="float: right;">Add Group Users</a>
                    <table class="table table-design-default">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Group Name</th>
                                <th>List Users</th>
                                <th>Add In Group</th>

                            </tr>
                        </thead>


                        <tbody>




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