@extends('layouts.vertical', ['title' => 'All Archived'])

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
                        <li class="breadcrumb-item active">List Archived</li>
                    </ol>
                </div>
                <h4 class="page-title">List Archived</h4>
            </div>
        </div>
    </div> -->
    <!-- end page title -->

    <div class="row mt-3">
        <div class="col-2">
            <ul class="nav_list">
                <li>
                    <a href="{{url('users/staff')}}"><span>List Staff</span></a>
                </li>
                <li class="activeli">
                    <a href="{{route('arcchiveStaff')}}"><span>Archived Staff</span></a>
                </li>
                <li>
                    <a href="{{route('expireStaffDocuments')}}"><span>Expired Documents</span></a>
                </li>
                <li>
                    <a href="{{route('teams')}}"><span>List Teams</span></a>
                </li>
                <li>
                    <a href="{{url('users/add-staff')}}"><span>New</span></a>
                </li>
            </ul>
        </div>
        <div class="col-10">
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
                    <?php
                    /*
                    <a href="javascript:;" data-toggle="modal" data-target="#sendEmail" class="btn btn-info" style="float: left;">Send Bulk Email</a>

                    <a href="{{route('listEmails')}}" class="btn btn-info dd" style="float: left;">List Emails</a>

                    <a href="javascript:;" data-toggle="modal" data-target="#sendSMS" class="btn btn-info sms" style="float: left;">Send SMS</a> 

                   <a href="{{route('listSMS')}}" class="btn btn-info dd" style="float: left;">List SMS</a> 
                    <a href="{{ url('users/add-staff') }}" class="btn btn-info" style="float: left;">Send Bulk SMS</a>

                    <a href="{{ route('exportStaff') }}" class="btn btn-info" style="float: right;">Export Data</a>
                    <a href="{{ route('clients.create') }}" class="btn btn-success adstaff" style="float: right;">Add Client</a>
                    */
                    ?>
                    <table class="table table-design-default">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Mobile</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Role</th>
                                <th>Action</th>


                            </tr>
                        </thead>


                        <tbody>
                            @if(count($arcchivedStaff) > 0 )
                            @foreach($arcchivedStaff as $key1=>$data)
                            <?php
                            // echo '<pre>';print_r($data->roles[0]->name);
                            ?>
                            <tr>
                                <td>{{$key1+1}}</td>
                                <td><a class="action">{{$data->first_name.' '.$data->middle_name.' '.$data->last_name}}</a></td>
                                <td>{{@$data->gender}}</td>
                                <td>{{$data->age}}</td>
                                <td>{{$data->mobile}}</td>
                                <td>{{$data->phone}}</td>
                                <td>{{@$data->email}}</td>
                                <td>{{@$data->address}}</td>
                                
                                <td><span class="badge bg-soft-success text-success">{{@ucfirst($data->roles[0]->name)}}</span></td>

                                </td>
                                <td>
                                    <a class="btn btn-success action_btn" href="{{route('unurchiveStaff',[$data->id])}}" onclick="return confirm('Are you sure you want to Unarchive this?')" style="margin: unset;
    font-size: 10px !important;">Unarchive</a>
                                </td>

                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="10" class="text-center">No data found</td>
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
<style>
    li.select2-selection__choice {
        color: black !important;
    }
</style>

<!-- Send Bulk Email -->
<div class="modal fade" id="sendEmail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myCenterModalLabel">Send Bulk Email</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form action="{{route('senBulkEmail')}}" method='post'>
                    @csrf
                    <input type="hidden" name="redirect" value="qc">
                    <div class="form-group">
                        <label>TO <code>*</code></label>
                        <select name="to[]" class="form-control js-example-basic-single" multiple="multiple" id="my-select">
                            @foreach($admins as $admin)
                            <option value="{{$admin->email}}">{{$admin->first_name.' '.$admin->last_name}}</option>
                            @endforeach
                        </select>
                        <input type="checkbox" id="select-all-checkbox"> Select All
                    </div>

                    <div class="form-group">
                        <label>Subject <code>*</code></label>
                        <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                    </div>

                    <div class="form-group">
                        <label>Message <code>*</code></label>
                        <textarea name="message" class="form-control" placeholder="Message" required></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->


<!-- Send Bulk SMS -->
<div class="modal fade" id="sendSMS" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myCenterModalLabel">Send Bulk SMS</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form action="{{route('senBulkSMS')}}" method='post'>
                    @csrf
                    <input type="hidden" name="redirect" value="qc">
                    <div class="form-group">
                        <label>TO <code>*</code></label>
                        <select name="to[]" class="form-control js-example-basic-single" multiple="multiple" id="my-select1">
                            @foreach($admins as $admin)
                            <option value="{{$admin->email}}">{{$admin->first_name.' '.$admin->last_name}}</option>
                            @endforeach
                        </select>
                        <input type="checkbox" id="select-all-checkbox1"> Select All
                    </div>

                    <div class="form-group">
                        <label>Message <code>*</code></label>
                        <textarea name="message" class="form-control" placeholder="Message" required></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

@endsection

@section('script')
<!-- Plugins js-->
<script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
<script src="{{asset('assets/libs/pdfmake/pdfmake.min.js')}}"></script>

<!-- Page js-->
<script src="{{asset('assets/js/pages/datatables.init.js')}}"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.js-example-basic-single').select2();

        $('#select-all-checkbox').on('change', function() {
            var selectAll = $(this).prop('checked');

            // Select or deselect all options based on the "Select All" checkbox state
            $('#my-select').find('option').prop('selected', selectAll);
            $('#my-select').trigger('change');
        });


        $('#select-all-checkbox1').on('change', function() {
            var selectAll = $(this).prop('checked');

            // Select or deselect all options based on the "Select All" checkbox state
            $('#my-select1').find('option').prop('selected', selectAll);
            $('#my-select1').trigger('change');
        });

    });
</script>
@endsection