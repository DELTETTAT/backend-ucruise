@extends('layouts.vertical', ['title' => 'Prices'])

@section('css')
<!-- Plugins css -->
<link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />

@endsection

@section('content')
<style>
    a.btn.btn-info.btn-sm {
        margin-bottom: 10px;
    }
</style>
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
                            <li class="breadcrumb-item active">List Prices</li>
                        </ol>
                    </div>
                    <h4 class="page-title">List Prices</h4>
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
                <li class="activeli">
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

                <li>
                    <a href="{{url('users/subscription')}}"><span>Subscription</span> </a>
                </li>
                <li>
                    <a href="{route('billing.index')}}"><span>Billing</span> </a>
                 </li>
                 <li>
                    <a href="{{url('users/subscription')}}"><span>Activity</span> </a>
                 </li>
            </ul>
        </div>
        <div class="col-10">



            <div class="card">
                <div>
                    <a href="javascript:;" data-toggle="modal" data-target="#addpricebook" class="btn btn-defult" style="float: right;">Add Price Book</a>
                </div>
                <div class="card-body">



                    @foreach($pricebooks as $key=>$data)
                    <div class="col-12 mt-2">
                        <h4><b> {{$data->name}}</b>
                            <a href="javascript:;" data-id="{{$data->id}}" class="editPriceBook"><i class="fa fa-edit text-warning" style="cursor: pointer;font-size: 12px;"></i></a>
                            <a href="<?php echo url('/users/deleteCategory'); ?>/{{$data->id}}/{{$data->getTable()}}" class="" onclick="return confirm('Are you sure you want to delete this?')"><i class="fas fa-trash text-danger" style="cursor: pointer;font-size: 12px;"></i></a>
                        </h4>
                        <!-- Add new price book -->
                        <?php
                        $priceBookData = $data->priceBookData;
                        ?>
                        @if(count($priceBookData) == 0)
                        <a href="javascript:;" class="btn btn-success addPrice" data-id="{{$data->id}}" style="float: right;margin-top: -40px;">Add Price</a>
                        @else
                        <a href="javascript:;" onClick="editPrice({{$key}})" class="btn btn-success" id="editprice{{$key}}" style="float: right;margin-top: -40px;">Edit</a>
                        <a href="javascript:;" onClick="cancelPrice({{$key}})" class="btn btn-danger" id="cancelprice{{$key}}" style="float: right;margin-top: -40px;margin-right: 50px; display:none;">Cancel</a>
                        <a href="javascript:;" data-toggle="modal" data-id="{{$data->id}}"  id="adddprice{{$key}}" class="btn btn-success addPrice" style="float: right;margin-top: -40px;margin-right: -20px; display:none;">Add Price</a>

                        @endif
                    </div>
                    <table class="table table-design-default" id="listprice{{$key}}">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Day of Week</th>
                                <th>Time</th>
                                <th>Per Hour</th>
                                <th>Per Ride</th>
                                <th>Reference Number (Hour)</th>
                                <th>Per Km</th>
                                <th>Reference Number</th>
                                <th>Effective Date</th>
                                <th>Multiplier</th>
                                <th>Action</th>

                            </tr>
                        </thead>


                        <tbody>

                            @foreach($priceBookData as $key1=>$data2)
                            <?php
                            // echo '<pre>';print_r($data->roles[0]->name);
                            ?>
                            <tr>
                                <td>{{$key1+1}}</td>

                                <td>{{$data2->day_of_week}}</td>
                                <td>{{$data2->start_time}} - {{$data2->end_time}}</td>
                                <td>{{@$data2->per_hour}}</td>
                                <td>{{@$data2->per_ride}}</td>
                                <td>{{@$data2->refrence_no_hr}}</td>
                                <td>{{@$data2->per_km}}</td>
                                <td>{{@$data2->refrence_no}}</td>
                                <td>
                                    @if($data2->effective_date < date('Y-m-d')) <span style="color:red">Expired</span>
                                        @else
                                        {{date('d-m-Y', strtotime($data2->effective_date))}}
                                        @endif
                                </td>
                                <td>{{@$data2->multiplier}}</td>





                                </td>
                                <td style="display:none1">
                                    <a href="javascript:;" data-id="{{$data2->id}}" class="editPrice"><i class="fa fa-edit text-warning" style="cursor: pointer;"></i></a>
                                    <a href="<?php echo url('/users/deleteCategory'); ?>/{{$data2->id}}/{{$data2->getTable()}}" class="" onclick="return confirm('Are you sure you want to delete this?')"><i class="fas fa-trash text-danger" style="cursor: pointer;"></i></a>

                                </td>

                            </tr>

                            @endforeach
                        </tbody>
                    </table>

                    <table class="table table-design-default editprice" id="Editprice{{$key}}" style="display: none;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Day of Week</th>
                                <th>Time</th>
                                <th>Per Hour</th>
                                <th>Per Ride</th>
                                <th>Reference Number (Hour)</th>
                                <th>Per Km</th>
                                <th>Reference Number</th>
                                <th>Effective Date</th>
                                <th>Multiplier</th>
                                <th>Action</th>

                            </tr>
                        </thead>


                        <tbody>

                            @foreach($priceBookData as $key1=>$data2)
                            <?php
                            // echo '<pre>';print_r($data->roles[0]->name);
                            ?>
                            <tr>
                                <td>{{$key1+1}}</td>

                                <td>
                                    <select name="day_of_week" class="form-control">
                                        <option value="weekdays" <?php if ($data2->day_of_week == "weekdays") {
                                                                        echo 'selected';
                                                                    } ?>>Weekdays (Mon-Fri)</option>
                                        <option value="saturday" <?php if ($data2->day_of_week == "saturday") {
                                                                        echo 'selected';
                                                                    } ?>>saturday</option>
                                        <option value="sunday" <?php if ($data2->day_of_week == "sunday") {
                                                                    echo 'selected';
                                                                } ?>>Sunday</option>
                                        <option value="public_holiday" <?php if ($data2->day_of_week == "public_holiday") {
                                                                            echo 'selected';
                                                                        } ?>>Public Holidays</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <input type="time" name="start_time" class="form-control" value="{{$data->start_time}}" placeholder="Start Time" required> -
                                        <input type="time" name="end_time" class="form-control" value="{{$data->end_time}}" placeholder="End Time" required>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="per_hour" class="form-control" value="{{$data->per_hour}}" placeholder="$10" required>
                                </td>
                                <td>
                                    <input type="text" name="per_ride" class="form-control" value="{{$data->per_ride}}" placeholder="$10" required>
                                </td>
                                <td>
                                    <input type="text" name="refrence_no_hr" class="form-control" value="{{$data->refrence_no_hr}}" placeholder="Reference Number (Hour)" required>
                                </td>
                                <td>
                                    <input type="text" name="per_km" class="form-control" value="{{$data->per_km}}" placeholder="$1" required>
                                </td>
                                <td>
                                    <input type="text" name="refrence_no" class="form-control" value="{{$data->refrence_no}}" placeholder="Reference Number" required>
                                </td>
                                <td>
                                    <input type="date" name="effective_date" class="form-control" value="{{$data->effective_date}}" placeholder="Effective Date" required>
                                </td>
                                <td>
                                    <select name="multiplier" class="form-control">
                                        <option value="1:1" <?php if ($data2->multiplier == "1:1") {
                                                                echo 'selected';
                                                            } ?>>1:1</option>
                                        <option value="2:1" <?php if ($data2->multiplier == "2:1") {
                                                                echo 'selected';
                                                            } ?>>2:1</option>
                                        <option value="1:2" <?php if ($data2->day_of_week == "1:2") {
                                                                echo 'selected';
                                                            } ?>>1:2</option>
                                        <option value="1:3" <?php if ($data2->day_of_week == "1:3") {
                                                                echo 'selected';
                                                            } ?>>1:3</option>
                                        <option value="1:4" <?php if ($data2->day_of_week == "1:4") {
                                                                echo 'selected';
                                                            } ?>>1:4</option>
                                        <option value="1:5" <?php if ($data2->day_of_week == "1:5") {
                                                                echo 'selected';
                                                            } ?>>1:5</option>
                                    </select>
                                </td>


                                </td>
                                <td style="display:none1">
                                    <a href="<?php echo url('/users/deleteCategory'); ?>/{{$data2->id}}/{{$data2->getTable()}}" class="" onclick="return confirm('Are you sure you want to delete this?')"><i class="fas fa-trash text-danger" style="cursor: pointer;"></i></a>

                                </td>

                            </tr>

                            <!-- Edit Price -->
                            <div class="modal fade" id="edit{{$key1.'_'.$key}}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title" id="myCenterModalLabel">Edit Price</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{route('prices.update',[$data2->id])}}" method='post'>
                                                <input type="hidden" name="_method" value="PUT">
                                                {{ csrf_field() }}
                                                <input type="text" name="price_book_id" value="{{$data2->id}}">


                                                <div class="form-group">
                                                    <label>Week Days <code>*</code></label>
                                                    <select name="day_of_week" class="form-control">
                                                        <option value="weekdays" <?php if ($data2->day_of_week == "weekdays") {
                                                                                        echo 'selected';
                                                                                    } ?>>Weekdays (Mon-Fri)</option>
                                                        <option value="saturday" <?php if ($data2->day_of_week == "saturday") {
                                                                                        echo 'selected';
                                                                                    } ?>>saturday</option>
                                                        <option value="sunday" <?php if ($data2->day_of_week == "sunday") {
                                                                                    echo 'selected';
                                                                                } ?>>Sunday</option>
                                                        <option value="public_holiday" <?php if ($data2->day_of_week == "public_holiday") {
                                                                                            echo 'selected';
                                                                                        } ?>>Public Holidays</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label>Start Time <code>*</code></label>
                                                    <input type="time" name="start_time" value="{{$data2->start_time}}" class="form-control" placeholder="Start Time" required>
                                                </div>

                                                <div class="form-group">
                                                    <label>End Time <code>*</code></label>
                                                    <input type="time" name="end_time" value="{{$data2->end_time}}" class="form-control" placeholder="End Time" required>
                                                </div>

                                                <div class="form-group">
                                                    <label>Per Hour <code>*</code></label>
                                                    <input type="text" name="per_hour" value="{{$data2->per_hour}}" class="form-control" placeholder="$10" required>
                                                </div>

                                                <div class="form-group">
                                                    <label>Reference Number (Hour) <code>*</code></label>
                                                    <input type="text" name="refrence_no_hr" class="form-control" placeholder="Reference Number (Hour)" value="{{$data2->refrence_no_hr}}" required>
                                                </div>

                                                <div class="form-group">
                                                    <label>Per Km<code>*</code></label>
                                                    <input type="text" name="per_km" class="form-control" placeholder="$1" value="{{$data2->per_km}}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Per Ride<code>*</code></label>
                                                    <input type="text" name="per_ride" class="form-control" placeholder="$1" value="{{$data2->per_ride}}" required>
                                                </div>

                                                <div class="form-group">
                                                    <label>Reference Number<code>*</code></label>
                                                    <input type="text" name="refrence_no" class="form-control" placeholder="Reference Number" value="{{$data2->refrence_no}}" required>
                                                </div>

                                                <div class="form-group">
                                                    <label>Effective Date<code>*</code></label>
                                                    <input type="date" name="effective_date" class="form-control" placeholder="Effective Date" value="{{$data2->effective_date}}" required>
                                                </div>

                                                <div class="form-group">
                                                    <label>Multiplier <code>*</code></label>
                                                    <select name="multiplier" class="form-control" required>
                                                        <option value="1:1" <?php if ($data2->multiplier == "1:1") {
                                                                                echo 'selected';
                                                                            } ?>>1:1</option>
                                                        <option value="2:1" <?php if ($data2->multiplier == "2:1") {
                                                                                echo 'selected';
                                                                            } ?>>2:1</option>
                                                        <option value="1:2" <?php if ($data2->multiplier == "1:2") {
                                                                                echo 'selected';
                                                                            } ?>>1:2</option>
                                                        <option value="1:3" <?php if ($data2->multiplier == "1:3") {
                                                                                echo 'selected';
                                                                            } ?>>1:3</option>
                                                        <option value="1:4" <?php if ($data2->multiplier == "1:4") {
                                                                                echo 'selected';
                                                                            } ?>>1:4</option>
                                                        <option value="1:5" <?php if ($data2->multiplier == "1:5") {
                                                                                echo 'selected';
                                                                            } ?>>1:5</option>

                                                    </select>
                                                </div>


                                                <div class="form-group">
                                                    <input type="hidden" name="type" value="1">
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


                            @endforeach
                        </tbody>
                    </table>

                    @endforeach

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


<!-- Add Price  Book-->
<div class="modal fade" id="addpricebook" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myCenterModalLabel">Add Price Book</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form action="{{route('pricebookStore')}}" method='post'>
                    @csrf
                    <input type="hidden" name="price_book_id" value="{{$data->id}}">

                    <div class="form-group">
                        <label>Name <code>*</code></label>
                        <input type="text" name="name" class="form-control" placeholder="Name" required>
                    </div>

                    <div class="form-group">
                        <label>External Id </label>
                        <input type="text" name="external_id" class="form-control" placeholder="External Id">
                    </div>

                    <div class="form-group">
                        <label>Fixed Price </label>
                        <input type="checkbox" name="fixed_price">
                    </div>

                    <div class="form-group">
                        <label>Provider Travel </label>
                        <input type="checkbox" name="provider_travel">
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
    function editPrice(data) {
        document.getElementById("listprice" + data).style.display = "none";
        document.getElementById("Editprice" + data).style.display = "inline-table";
        document.getElementById("cancelprice" + data).style.display = "block";
        document.getElementById("editprice" + data).style.display = "none";
        document.getElementById("adddprice" + data).style.display = "block";

    }

    function cancelPrice(data) {
        document.getElementById("listprice" + data).style.display = "inline-table";
        document.getElementById("Editprice" + data).style.display = "none";
        document.getElementById("cancelprice" + data).style.display = "none";
        document.getElementById("editprice" + data).style.display = "block";
        document.getElementById("adddprice" + data).style.display = "none";

    }
    $(document).ready(function() {

        $('.sss1').DataTable();

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

@include('price.modals')
@include('price.scripts')

@endsection