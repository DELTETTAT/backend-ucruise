@extends('layouts.vertical', ['title' => 'Report Compliance'])

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
                        <li class="breadcrumb-item active">Report Compliance</li>
                    </ol>
                </div>
                <h4 class="page-title">Report Compliance</h4>
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

                    <!-- <a href="javascript:;" data-toggle="modal" data-target="#sendEmail" class="btn btn-info" style="float: left;">Send Bulk Email</a>

                    <a href="{{route('listEmails')}}" class="btn btn-info dd" style="float: left;">List Emails</a>

                    <a href="javascript:;" data-toggle="modal" data-target="#sendSMS" class="btn btn-info sms" style="float: left;">Send SMS</a> -->

                    <!-- <a href="{{route('listSMS')}}" class="btn btn-info dd" style="float: left;">List SMS</a> -->
                    <!-- <a href="{{ url('users/add-staff') }}" class="btn btn-info" style="float: left;">Send Bulk SMS</a> -->

                    <!-- <a href="{{ route('exportStaff') }}" class="btn btn-info" style="float: right;">Export Data</a> -->
                    <!-- <a href="{{ route('clients.create') }}" class="btn btn-success adstaff" style="float: right;">Add Client</a>
                      -->
                    <table class="table table-design-default">
                        <thead>
                            <tr>
                                <th>Staff Name</th>
                                <?php $length = 0; ?>
                                @foreach($compliance as $key1=>$data)
                                <?php $length += $key1; ?>
                                <th>{{$data->name}}</th>

                                @endforeach
                            </tr>
                        </thead>


                        <tbody>
                            @if(count($doc) > 0 )
                            @foreach($doc as $key1=>$data)
                            <?php
                            // echo  $length; 
                            $catArray = explode(',', $data->names);
                            $expire = explode(',', $data->expire);

                            $userData = DB::table('users')->where('id', $data->staff_id)->first();
                            ?>
                            <tr>
                                <td>{{@$userData->first_name .' '.@$userData->last_name}}</td>
                                @for($i=0; $i < $compliance->count();$i++) <!-- <td>{{$compliance[$i]->name}}</td> -->
                                    <td>
                                        <?php
                                        $name = $compliance[$i]->name;
                                        if (in_array($name, $catArray)) {

                                            $expData = DB::table('staff_documents')->where('staff_id', $data->staff_id)->where(DB::raw('lower(category)'), strtolower($name))->first();
                                            //dd($expData->expire);


                                            if ($expData->expire > date('Y-m-d')) {
                                                echo $expData->expire;
                                            } else {
                                                echo 'Expired';
                                            }

                                            //echo $name.'-'.$i;
                                        }

                                        //echo $expire[$i];
                                        ?>

                                    </td>
                                    @endfor


                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="2" class="text-center">No data found</td>
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