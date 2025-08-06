@extends('layouts.vertical', ['title' => 'Pay Groups'])

@section('css')
<!-- Plugins css -->
<link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />

@endsection

@section('content')
<style>
    /* .card-body {
    flex: 1 1 auto;
    min-height: 1px;
    padding: 1.5rem;
    overflow: scroll;
}  */
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
                            <li class="breadcrumb-item active">List Pay Groups</li>
                        </ol>
                    </div>
                    <h4 class="page-title">List Pay Groups</h4>
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
                <li class="activeli">
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
                    <a href="{{route('billing.index')}}"><span>Billing</span> </a>
                 </li>
                 <li>
                    <a href="{{url('users/subscription')}}"><span>Activity</span> </a>
                 </li>
            </ul>
        </div>
        <div class="col-10">



            <div class="card">
                <div class="card-body">

                    <a href="javascript:;" data-toggle="modal" data-target="#addPayGroup" class="btn btn-defult" style="float: right;">Add Pay Group</a>

                    @forelse($paygroup as $key=>$data)
                    <div class="col-12 mt-5">
                        <h4><b> {{$data->name}}</b>
                            <a data-toggle="modal" data-target="#editPayGroup{{$key}}" href="javascript:;"><i class="fa fa-edit text-warning" style="cursor: pointer;font-size: 12px;"></i></a>
                            <a href="<?php echo url('/users/deleteCategory'); ?>/{{$data->id}}/{{$data->getTable()}}" class="" onclick="return confirm('Are you sure you want to delete this?')"><i class="fas fa-trash text-danger" style="cursor: pointer;font-size: 12px;"></i></a>
                        </h4>
                        <!-- Add new price book -->
                        <a href="javascript:;" data-toggle="modal" data-target="#addPayItem{{$key}}" class="btn btn-defult" style="float: right;margin-top: -40px;">New Pay Item</a>
                    </div>
                    <table class="table table-design-default">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Day of Week</th>
                                <th>Time</th>
                                <th>Effective Date</th>
                                <th>Xero Pay Item</th>
                                <th>Action</th>

                            </tr>
                        </thead>


                        <tbody>
                            <?php
                            $payGroupData = App\Models\Paygroupdata::where('paygroup_id', $data->id)->orderBy('id', 'DESC')->get();

                            ?>
                            @forelse($payGroupData as $key1=>$data2)
                            <?php
                            // echo '<pre>';print_r($data->roles[0]->name);
                            ?>
                            <tr>
                                <td>{{$key1+1}}</td>

                                <td>{{$data2->day_of_week}}</td>
                                <td>{{$data2->start_time}} - {{$data2->end_time}}</td>

                                <td>
                                    @if($data2->effective_date < date('Y-m-d')) <span style="color:red">Expired</span>
                                        @else
                                        {{date('d-m-Y', strtotime($data2->effective_date))}}
                                        @endif
                                </td>
                                <td>{{@$data2->Xero_pay_item}}</td>





                                </td>
                                <td style="display:none1">
                                    <a data-toggle="modal" data-target="#edit{{$key1.'_'.$key}}" href="javascript:;"><i class="fa fa-edit text-warning" style="cursor: pointer;"></i></a>
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
                                            <form action="{{route('award_group.update',[$data2->id])}}" method='post'>
                                                <input type="hidden" name="_method" value="PUT">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="price_book_id" value="{{$data2->id}}">


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
                                                    <label>Effective Date<code>*</code></label>
                                                    <input type="date" name="effective_date" class="form-control" placeholder="Effective Date" value="{{$data2->effective_date}}" required>
                                                </div>

                                                <div class="form-group">
                                                    <label>Xero Pay Item<code>*</code></label>
                                                    <input type="text" name="Xero_pay_item" class="form-control" placeholder="Effective Date" value="{{$data2->Xero_pay_item}}" required>
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
                            @empty
                            <tr>
                                <td></td>
                                <td></td>
                                <td>No Data Found</td>
                                <td></td>
                                <td></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>




                    <!-- Add Price -->
                    <div class="modal fade" id="addPayItem{{$key}}" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myCenterModalLabel">Add New Pay Item</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{route('award_group.store')}}" method='post'>
                                        @csrf
                                        <input type="hidden" name="paygroup_id" value="{{$data->id}}">


                                        <div class="form-group">
                                            <label>Week Days <code>*</code></label>
                                            <select name="day_of_week" class="form-control">
                                                <option value="weekdays">Weekdays (Mon-Fri)</option>
                                                <option value="saturday">saturday</option>
                                                <option value="sunday">Sunday</option>
                                                <option value="public_holiday">Public Holidays</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Start Time <code>*</code></label>
                                            <input type="time" name="start_time" class="form-control" placeholder="Start Time" required>
                                        </div>

                                        <div class="form-group">
                                            <label>End Time <code>*</code></label>
                                            <input type="time" name="end_time" class="form-control" placeholder="End Time" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Effective Date<code>*</code></label>
                                            <input type="date" name="effective_date" class="form-control" placeholder="Effective Date" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Xero Pay Item<code>*</code></label>
                                            <input type="text" name="Xero_pay_item" class="form-control" placeholder="Xero Pay Item" required>
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


                    <!-- Edit Price book -->

                    <div class="modal fade" id="editPayGroup{{$key}}" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myCenterModalLabel">Edit Pay Group</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{route('payGroupUpdate',[$data->id])}}" method='post'>
                                        <input type="hidden" name="_method" value="PUT">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="price_book_id" value="{{$data->name}}">


                                        <div class="form-group">
                                            <label>Name <code>*</code></label>
                                            <input type="text" name="name" value="{{$data->name}}" class="form-control" placeholder="Name" required>
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

                    @empty
                    <div class="col-12 mt-5 text-center">
                        <h4><b> No Data Found</b></h4>
                    </div>

                    @endforelse

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
<div class="modal fade" id="addPayGroup" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myCenterModalLabel">Add Pay Group</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form action="{{route('payGroupStore')}}" method='post'>
                    @csrf
                    <input type="hidden" name="price_book_id" value="{{$data->id}}">

                    <div class="form-group">
                        <label>Name <code>*</code></label>
                        <input type="text" name="name" class="form-control" placeholder="Pay Group Name" required>
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
@endsection