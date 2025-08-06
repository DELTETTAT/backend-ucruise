<!-- edit price book modal -->
<div id="editPriceBook" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myCenterModalLabel">Edit Price Book</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form action="{{route('priceBookUpdate')}}" method='post'>
                    <input type="hidden" name="_method" value="PUT">
                    {{ csrf_field() }}
                    <input type="hidden" name="id" value="">

                    <div class="form-group">
                        <label>Name <code>*</code></label>
                        <input type="text" name="name" value="" class="form-control" placeholder="Name" required>
                    </div>

                    <div class="form-group">
                        <label>External Id </label>
                        <input type="text" value="" name="external_id" class="form-control" placeholder="External Id">
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



<!-- Add Price Modal -->
<div class="modal fade" id="addPrice" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myCenterModalLabel">Add Price</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form action="{{route('prices.store')}}" method='post'>
                    @csrf
                    <input type="hidden" name="price_book_id" value="{{$data->id}}">


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
                        <label>Per Hour <code>*</code></label>
                        <input type="text" name="per_hour" class="form-control" placeholder="$10" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Per Ride <code>*</code></label>
                        <input type="text" name="per_ride" class="form-control" placeholder="$10" required>
                    </div>

                    <div class="form-group">
                        <label>Reference Number (Hour) <code>*</code></label>
                        <input type="text" name="refrence_no_hr" class="form-control" placeholder="Reference Number (Hour)" required>
                    </div>

                    <div class="form-group">
                        <label>Per Km<code>*</code></label>
                        <input type="text" name="per_km" class="form-control" placeholder="$1" required>
                    </div>

                    <div class="form-group">
                        <label>Reference Number<code>*</code></label>
                        <input type="text" name="refrence_no" class="form-control" placeholder="Reference Number" required>
                    </div>

                    <div class="form-group">
                        <label>Effective Date<code>*</code></label>
                        <input type="date" name="effective_date" class="form-control" placeholder="Effective Date" required>
                    </div>

                    <div class="form-group">
                        <label>Multiplier <code>*</code></label>
                        <select name="multiplier" class="form-control">
                            <option value="1:1">1:1</option>
                            <option value="2:1">2:1</option>
                            <option value="1:2">1:2</option>
                            <option value="1:3">1:3</option>
                            <option value="1:4">1:4</option>
                            <option value="1:5">1:5</option>

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


<!-- Edit Price -->
<div class="modal fade" id="editPrice" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myCenterModalLabel">Edit Price</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form action="{{route('prices.update',[1])}}" method='post'>
                    <input type="hidden" name="_method" value="PUT">
                    {{ csrf_field() }}
                    <input type="hidden" name="price_book_id" value="{{$data2->id}}">


                    <div class="form-group">
                        <label>Week Days <code>*</code></label>
                        <select name="day_of_week" id="day_of_week" class="form-control">
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
                        <label>Per Ride <code>*</code></label>
                        <input type="text" name="per_ride" value="{{$data2->per_ride}}" class="form-control" placeholder="$10" required>
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
                        <label>Reference Number<code>*</code></label>
                        <input type="text" name="refrence_no" class="form-control" placeholder="Reference Number" value="{{$data2->refrence_no}}" required>
                    </div>

                    <div class="form-group">
                        <label>Effective Date<code>*</code></label>
                        <input type="date" name="effective_date" class="form-control" placeholder="Effective Date" value="{{$data2->effective_date}}" required>
                    </div>

                    <div class="form-group">
                        <label>Multiplier <code>*</code></label>
                        <select name="multiplier"  id="multiplier" class="form-control" required>
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