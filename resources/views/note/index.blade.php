@extends('layouts.vertical', ['title' => 'All Note'])

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
                            <li class="breadcrumb-item active">List {{\Request::route()->getName()}}</li>
                        </ol>
                    </div>
                    <h4 class="page-title"><a href="{{route('account')}}"><i class="fa fa-arrow-left" style="cursor: pointer;"></i></a> Lists {{\Request::route()->getName()}}</h4>
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
                     
                    <a href="{{ route('Add Notes') }}" class="btn btn-success" style="float: right;">Add Notes</a>
                     
                    <br>
                    <br>
                    <br>
                        <table id="basic-datatable" class="table dt-responsive table-hover table-bordered nowrap w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Heading</th>
                                    <th>Mandatory</th>
                                    <th>Action</th>
                                    
                                     
                                </tr>
                            </thead>
                        
                        
                            <tbody>
                            @foreach($notes as $key1=>$data)
                            <?php
                               // echo '<pre>';print_r($data->roles[0]->name);
                            ?>
                                <tr>
                                    <td>{{$key1+1}}</td>
                                    <td>{{$data->heading}}</td>
                                    <td>{{($data->mandatory ==1) ? 'Yes' : 'No' }}</td>
                                    
                                    <td style="display:none1">
                                        <ul style="padding: initial;">
                                            
                                                <li title="Edit" style="display:inline;"><a href="{{url('/users/edit-note',[$data->id])}}" class="btn btn-sm btn-info"><i class="fa fa-edit" style="cursor: pointer;"></i></a></li>
                                                
                                            
                                                <li title="Delete" style="display:inline-block;"><a href="<?php echo url('/users/deleteCategory');?>/{{$data->id}}/{{$data->getTable()}}" class="btn btn-sm btn-danger"  onclick="return confirm('Are you sure you want to delete this?')"><i class="fas fa-trash" style="cursor: pointer;"></i></a>
                                                </li>
                                             
                                        </ul>
                                    </td>
                                     
                                </tr>
                            @endforeach  
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