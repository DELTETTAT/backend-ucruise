@extends('layouts.vertical', ['title' => 'Schedule'])
@section('content')
<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
<style>
   .select2-container--default .select2-results__option--highlighted[aria-selected] {
      background-color: #e9e9e9;
   }

   #pickUpMap {
      height: 400px;
      width: 100%;
   }

   #dropOffMap {
      height: 400px;
      width: 100%;
   }
</style>
<!-- Start Content-->
<div class="container-fluid">

   <!-- start page title -->
   <div class="row">
      <div class="col-12">
         <div class="page-title-box">
            <div class="page-title-right">
               <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Schedule</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Edit Schedule</a></li>
               </ol>
            </div>
            <h4 class="page-title">Edit Schedule</h4>
         </div>
      </div>
   </div>
   <!-- end page title -->
   <div class="row">
      <div class="col-lg-12 card">
         @if ($errors->any())
         <div class="alert alert-danger">
            <ul>
               @foreach ($errors->all() as $error)
               <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
         @endif

         <form method="POST" action="{{ route('updateSchedule', ['id' => $schedule->id]) }}" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="card-body">
               <div class="form-group">
                  <div class="row">
                     <input type="hidden" name="current_date" value="{{$date}}">
                     <div class="col-sm-6 mt-2 role ">

                        <!-- time and location -->

                        <div class="row mt-2">

                           <div class="col-sm-12 mt-3 role">
                              <label for="exampleInputName1">Driver with vehicle<code>*</code></label>
                              <select class="form-control js-example-basic-single" name="driver">
                                 <option disabled="">Select</option>
                                 @foreach($drivers as $driver)
                                 <option value="{{$driver->driver_id}}" {{($driver->driver_id ==
                                    $schedule->driver_id)?'selected':''}}>{{$driver->first_name . ' ' .
                                    $driver->last_name}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$driver->name}} ({{$driver->seats}} seats - ₹ {{$driver->fare}})
                                 </option>
                                 </option>
                                 @endforeach
                              </select>
                           </div>
                           <!-- <div class="col-sm-6 mt-3 role">
                              <label for="exampleInputName1">Vehicle <code>*</code></label>
                              <select class="form-control js-example-basic-single" id="vehicleSelect" name="vehicle">
                                 <option disabled="">Select</option>
                                 @foreach($vehicles as $vehicle)
                                 <option value="{{$vehicle->id}}" {{($vehicle->id ==
                                    $schedule->vehicle_id)?'selected':''}} data-capacity="{{$vehicle->seats}}">{{$vehicle->name}} ({{$vehicle->seats}} seats-
                                    ₹{{$vehicle->fare}})
                                 </option>
                                 @endforeach
                              </select>
                           </div> -->

                           <div class="col-sm-6 mt-3 role">
                              <label for="exampleInputName1">Date <code>*</code></label>
                           </div>

                           <div class="col-sm-6 mt-3 role text-right">
                              <input type="date" name="date" class="form-control" value="{{$schedule->date}}" placeholder="Date" readonly>
                           </div>


                           <div class="col-sm-6 mt-3 role">
                              <label for="exampleInputName1">Shift Finishes Next Day <code>*</code></label>
                           </div>

                           <div class="col-sm-6 mt-3 role text-right">
                              <input type="checkbox" name="shift_finishes_next_day" id="select-all-checkbox" {{$schedule->shift_finishes_next_day == 1 ? 'checked' : ''}}>
                           </div>

                           <div class="col-sm-6 mt-3 role">
                              <label for="exampleInputName1">Break time in minutes <code>*</code></label>
                           </div>

                           <div class="col-sm-6 mt-3 role text-right">
                              <input type="number" name="break_time_in_minutes" value="{{$schedule->break_time_in_minutes}}" class="form-control" placeholder="Enter Minutes">
                           </div>


                           <!-- Shift -->

                           <div class="col-sm-6 mt-3 role">
                              <label for="exampleInputName1">Shift Type <code>*</code></label>
                              <select class="form-control js-example-basic-single" name="shift_types" id="shiftType">
                                 <option disabled="">Select</option>
                                 @foreach($shiftTypes as $shiftType)
                                 <option value="{{$shiftType->id}}" {{$schedule->shift_type_id == $shiftType->id ?
                                    'selected' : ''}}>{{$shiftType->name}}</option>
                                 @endforeach
                              </select>
                           </div>

                           <div class="col-sm-6 mt-3 role">
                              <label for="exampleInputName1">Pricebook <code>*</code></label>
                              <select class="form-control js-example-basic-single" name="pricebook">
                                 <option disabled="">Select</option>
                                 @foreach($pricebooks as $pricebook)
                                 <option value="{{$pricebook->id}}" {{$schedule->pricebook_id == $pricebook->id ?
                                    'selected' : ''}}>{{$pricebook->name}}</option>
                                 @endforeach
                              </select>
                           </div>


                           <div class="col-sm-3 mt-3 role">
                              <label for="exampleInputName1">Ignore Staff Count <code>*</code></label>
                           </div>

                           <div class="col-sm-3 mt-3 role">
                              <input type="checkbox" name="ignore_staff_count" id="select-all-checkbox" {{$schedule->ignore_staff_count == 1 ? 'checked' : ''}}>
                           </div>

                           <div class="col-sm-3 mt-3 role">
                              <label for="exampleInputName1">Confirmation Required <code>*</code></label>
                           </div>
                           <div class="col-sm-3 mt-3 role">
                              <input type="checkbox" name="confirmation_required" id="select-all-checkbox" {{$schedule->confirmation_required == 1 ? 'checked' : ''}}>
                           </div>




                           <div class="col-12">
                              <div class="row">

                                 @if($schedule->is_repeat == 1)
                                 <div class="col-sm-6 mt-3">
                                    <label for="exampleInputName1">Apply to all Future occurences <code>*</code></label>
                                 </div>

                                 <div class="col-sm-6 mt-3">
                                    <input type="checkbox" name="apply_to_future" id="apply_to_future">
                                 </div>
                                 @endif

                                 @foreach($carers as $carer)
                                 <input type="hidden" name="longitude" id="longitude_{{$carer->id}}" value="{{$carer->longitude}}">
                                 <input type="hidden" name="latitude" id="latitude_{{$carer->id}}" value="{{$carer->latitude}}">
                                 @endforeach
                                 @php
                                 $pick_drop_carers = [];

                                 foreach ($schedule->carers as $carer) {

                                 if (isset($pick_drop_carers[$carer->shift_type])) {
                                 $pick_drop_carers[$carer->shift_type][] = $carer->carer_id;

                                 } else {
                                 $pick_drop_carers[$carer->shift_type] = [$carer->carer_id];

                                 }
                                 }
                                 @endphp

                                 <div class="col-sm-6 mt-3 role" id="pickupTime">
                                    <label for="exampleInputName1">PickUp Time <code>*</code></label>
                                    <input type="time" name="start_time" class="form-control" value="{{date('H:i', strtotime($schedule->start_time))}}" placeholder="Date">

                                    <div class="row">

                                       <div class="col-12 mt-1 role NotJobBoard">
                                          <label for="exampleInputName1">Pick Up Staffs <code>*</code></label>
                                          <select class="form-control js-example-basic-single pickUpCarerSelect" multiple="multiple" name="pickUpCarer[]" id="pickUpCarerSelect">
                                             <option disabled="">Select</option>

                                             @foreach($carers as $carer)
                                             <option value="{{$carer->id}}" {{(array_key_exists("pick", $pick_drop_carers) && in_array($carer->id, $pick_drop_carers["pick"])) ? 'selected' : ''}}>{{$carer->first_name}}</option>
                                             @endforeach
                                          </select>
                                       </div>

                                       <div class="pickUpCarerTimes col-12 mt-1 NotJobBoard" data-holiday="{{$holiday}}">
                                          <!-- carer times -->

                                          @foreach($schedule->carers as $carer)
                                          @if($carer->shift_type == "pick")
                                          <div class="row" id="pickUpCarerTime{{$carer->carer_id}}">
                                             <input type="hidden" name="pickUpCarerTimes[{{$carer->carer_id}}][carer_id]" value="{{$carer->carer_id}}">
                                             <!-- @if($holiday == "yes")
                                             <div class="col-sm-6">
                                                <label for="exampleInputName1">Make this day working <code>*</code></label>
                                             </div>
                                             <div class="col-sm-6">
                                                <input type="checkbox" name="carerTimes[{{$carer->carer_id}}][make_working]" id="make_working_{{$carer->carer_id}}">
                                             </div>
                                             @endif -->
                                          </div>
                                          @endif
                                          @endforeach

                                       </div>

                                       <div class="col-sm-12 mt-3 role">
                                          <div id="pickUpMap"></div>
                                          <div id="pickUpDirectionsPanel"></div>
                                       </div>

                                       <div class="col-sm-6 mt-3 role NotJobBoard">
                                          <label for="exampleInputName1">Notify Carer <code>*</code></label>
                                       </div>

                                       <div class="col-sm-6 mt-3 role NotJobBoard">
                                          <input type="checkbox" name="notify_carer" id="select-all-checkbox" {{$schedule->notify_carer == 1 ? 'checked' : ''}}>
                                       </div>

                                    </div>

                                 </div>
                                 <div class="col-sm-6 mt-3 role" id="dropoffTime">
                                    <label for="exampleInputName1">DropOff Time <code>*</code></label>
                                    <input type="time" name="end_time" class="form-control" value="{{date('H:i', strtotime($schedule->end_time))}}" placeholder="Date">

                                    <div class="row">

                                       <div class="col-12 mt-1 role NotJobBoard">
                                          <label for="exampleInputName1">Drop Off Staffs <code>*</code></label>
                                          <select class="form-control js-example-basic-single dropOffCarerSelect" multiple="multiple" name="dropOffCarer[]" id="dropOffCarerSelect">
                                             <option disabled="">Select</option>
                                             @foreach($carers as $carer)
                                             <option value="{{$carer->id}}" {{(array_key_exists("drop", $pick_drop_carers) && in_array($carer->id, $pick_drop_carers["drop"])) ? 'selected' : ''}}>{{$carer->first_name}}</option>
                                             @endforeach
                                          </select>
                                       </div>

                                       <div class="dropOffCarerTimes col-12 mt-1 NotJobBoard" data-holiday="{{$holiday}}">
                                          <!-- carer times -->

                                          @foreach($schedule->carers as $carer)
                                          @if($carer->shift_type == "drop")
                                          <div class="row" id="dropOffCarerTime{{$carer->carer_id}}">
                                             <input type="hidden" name="dropOffCarerTimes[{{$carer->carer_id}}][carer_id]" value="{{$carer->carer_id}}">
                                             <!-- @if($holiday == "yes")
                                             <div class="col-sm-6">
                                                <label for="exampleInputName1">Make this day working <code>*</code></label>
                                             </div>
                                             <div class="col-sm-6">
                                                <input type="checkbox" name="carerTimes[{{$carer->carer_id}}][make_working]" id="make_working_{{$carer->carer_id}}">
                                             </div>
                                             @endif -->
                                          </div>
                                          @endif
                                          @endforeach

                                       </div>

                                       <div class="col-sm-12 mt-3 role">
                                          <div id="dropOffMap"></div>
                                          <div id="dropOffDirectionsPanel"></div>
                                       </div>

                                       <div class="col-sm-6 mt-3 role NotJobBoard">
                                          <label for="exampleInputName1">Notify Carer <code>*</code></label>
                                       </div>

                                       <div class="col-sm-6 mt-3 role NotJobBoard">
                                          <input type="checkbox" name="notify_carer" id="select-all-checkbox" {{$schedule->notify_carer == 1 ? 'checked' : ''}}>
                                       </div>

                                    </div>
                                 </div>
                              </div>
                           </div>

                        </div>
                     </div>

                     <div class="col-sm-6 mt-2 role">

                        <!-- tasks -->
                        <div class="row">
                           <div class="col-sm-6 mt-3 role">
                              <div class="row">
                                 <div class="col-sm-3">
                                    <h5>Tasks</h5>
                                 </div>
                                 <div class="col-sm-4">
                                    <input type="text" name="task_name" id="task_name" class="form-control" placeholder="Add Task">
                                 </div>
                                 <div class="col-sm-3">
                                    <label for="exampleInputName1">is Mandatory <code>*</code></label>
                                    <input type="checkbox" name="is_mandatory" id="is_mandatory">
                                 </div>
                                 <div class="col-sm-2">
                                    <a href="javascript:void(0)" class="btn btn-primary addTask">Add</a>
                                 </div>
                              </div>
                              <div class="row">
                                 <div class="col-sm-12 mt-3 role">
                                    <table class="table">
                                       <thead>
                                          <tr>
                                             <th scope="col">Name</th>
                                             <th scope="col">Is Mandatory</th>
                                             <th scope="col">Remove</th>
                                          </tr>
                                       </thead>
                                       <tbody class="tasks">
                                          @foreach($schedule->tasks as $task)
                                          <tr>
                                             <td>
                                                <input type="hidden" value="{{$task->name}}" name="tasks[{{$task->id}}][name]" />
                                                <input type="hidden" value="{{$task->is_mandatory == 1 ? 'on' : 'off'}}" name="tasks[{{$task->id}}][is_mandatory]" />
                                                {{$task->name}}
                                             </td>
                                             <td>{{$task->is_mandatory == 1 ? 'on' : 'off'}}</td>
                                             <td>@mdo</td>
                                          </tr>
                                          @endforeach
                                       </tbody>
                                    </table>
                                 </div>
                              </div>
                           </div>
                        </div>


                        <label for="exampleInputName1">Instructions <code>*</code></label>
                        <textarea name="instructions" class="form-control" cols="30" rows="10">{{$schedule->instructions}}</textarea>
                        <div class="row">
                           <div class="col-sm-12 mt-3 role">

                              <table class="table">
                                 <thead>
                                    <tr>
                                       <h4>Events</h4>
                                    </tr>
                                 </thead>
                                 <tbody class="events">

                                    @if($leaveRequests)
                                    @foreach ($leaveRequests as $leaveRequest)

                                    @if($leaveRequest->status==0)

                                    <td>
                                       <label for="exampleInputName1"><span class="border">{{$leaveRequest->start_date}}</span> {{$leaveRequest->first_name}} {{$leaveRequest->last_name}}
                                          has leave request<code>*</code></label>
                                       <button style="border: none"><a href="{{ route('approve-leave', ['id' => $leaveRequest->id]) }}"><i class="mdi mdi-check-circle"></i>Accept</a></button>&nbsp;&nbsp;&nbsp;
                                       <button style="border: none"> <a href="{{ route('reject-leave', ['id' => $leaveRequest->id]) }}" style="color:red"><i class="mdi mdi-alpha-x-circle"></i>Reject</a></button>
                                    </td>
                                    </tr>

                                    @else
                                    <tr>
                                       <td>

                                          <label for="exampleInputName1"><span class="border">{{$leaveRequest->start_date}}</span> {{$leaveRequest->first_name}} {{$leaveRequest->last_name}}
                                             leave request has been {{$leaveRequest->status}}</label>

                                       </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                    @endif
                                    @if($event_carers)
                                    @foreach($event_carers as $event_carer)
                                    <tr>
                                       <td><label for="exampleInputName1"><span class="border">{{$event_carer->date}}</span> {{$event_carer->first_name}} has shift change request</label><button style="border: none"><a href="{{ route('approve-shiftchange', ['id' => $event_carer->id]) }}"><i class="mdi mdi-check-circle"></i>Accept</a></button>&nbsp;&nbsp;&nbsp;
                                          <button style="border: none"> <a href="{{ route('reject-shiftchange', ['id' => $event_carer->id]) }}" style="color:red"><i class="mdi mdi-alpha-x-circle"></i>Reject</a></button>
                                       </td>
                                    </tr>
                                    @endforeach
                                    @endif

                                 </tbody>
                              </table>





                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="card-footer">
                  <button type="submit" class="btn btn-success" value="1" name="exit">Update</button>

                  <a href="javascript:;" class="btn btn-danger" onclick="history.back()">Back</a>
               </div>
         </form>
      </div>
   </div>
</div>




@endsection
@section('script')

<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
   const GOOGLE_API_KEY = '{{ env("GOOGLE_API_KEY") }}';
   var script = document.createElement('script');
   script.src = "https://maps.googleapis.com/maps/api/js?key=" + GOOGLE_API_KEY + "&callback=initMap&libraries=places&v=weekly";
   script.defer = true;
   document.head.appendChild(script);
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
   $(document).ready(function() {

      var selectedShiftType = $(shiftType).val();

      if (selectedShiftType == 1) {
         $('#pickupTime').show();
         $('#dropoffTime').hide();
      }
      if (selectedShiftType == 3) {
         $('#dropoffTime').show();
         $('#pickupTime').hide();
      }
      if (selectedShiftType == 2) {
         $('#pickupTime').show();
         $('#dropoffTime').show();
      }

      $('#shiftType').change(function() {
         var selectedShiftType = $(this).val();

         if (selectedShiftType == 1) {
            $('#dropoffTime').hide();
            $('#pickupTime').show();
            calculateAndDisplayRoute(pick_up_waypoint_array, true);
         } else if (selectedShiftType == 2) {
            $('#dropoffTime').show();
            $('#pickupTime').show();
            calculateAndDisplayRoute(pick_up_waypoint_array, true);
            calculateAndDisplayRoute(drop_off_waypoint_array, false);
         } else {
            $('#dropoffTime').show();
            $('#pickupTime').hide();
            calculateAndDisplayRoute(drop_off_waypoint_array, false);
         }
      });

   });
</script>
<script>
   var directionsService;
   var pickUpDirectionsRenderer;
   var dropOffDirectionsRenderer;

   async function initMap() {

      const {
         Map
      } = await google.maps.importLibrary("maps");
      const {
         AdvancedMarkerElement,
         PinElement
      } = await google.maps.importLibrary(
         "marker",
      );
      const {
         Place
      } = await google.maps.importLibrary("places");
      const pickUpMap = new Map(document.getElementById("pickUpMap"), {
         center: {
            lat: <?php echo @$company_details['latitude'] ?>,
            lng: <?php echo @$company_details['longitude'] ?>
         },
         zoom: 13,
         mapId: "4504f8b37365c3d0",
      });

      const dropOffMap = new Map(document.getElementById("dropOffMap"), {
         center: {
            lat: <?php echo @$company_details['latitude'] ?>,
            lng: <?php echo @$company_details['longitude'] ?>
         },
         zoom: 13,
         mapId: "4504f8b37365c3d0",
      });

      directionsService = new google.maps.DirectionsService();
      pickUpDirectionsRenderer = new google.maps.DirectionsRenderer();
      dropOffDirectionsRenderer = new google.maps.DirectionsRenderer();

      pickUpDirectionsRenderer.setMap(pickUpMap);
      dropOffDirectionsRenderer.setMap(dropOffMap);

      calculateAndDisplayRoute(pick_up_waypoint_array, true);
      calculateAndDisplayRoute(drop_off_waypoint_array, false);
   }

   async function calculateAndDisplayRoute(wayPointArr, type = null) {
      if (type == null) {
         return;
      }
      // const {
      //    Map
      // } = await google.maps.importLibrary("maps");
      // const {
      //    AdvancedMarkerElement,
      //    PinElement
      // } = await google.maps.importLibrary(
      //    "marker",
      // );
      // const {
      //    Place
      // } = await google.maps.importLibrary("places");

      // directionsService = new google.maps.DirectionsService();

      // if (type == true) {

      //    pickUpDirectionsRenderer = new google.maps.DirectionsRenderer();
      //    const map = new Map(document.getElementById("pickUpMap"), {
      //       center: {
      //          lat: <?php echo @$company_details['latitude'] ?>,
      //          lng: <?php echo @$company_details['longitude'] ?>
      //       },
      //       zoom: 13,
      //       mapId: "4504f8b37365c3d0",
      //    });
      //    pickUpDirectionsRenderer.setMap(map);

      // } else {

      //    dropOffDirectionsRenderer = new google.maps.DirectionsRenderer();
      //    const map = new Map(document.getElementById("dropOffMap"), {
      //       center: {
      //          lat: <?php echo @$company_details['latitude'] ?>,
      //          lng: <?php echo @$company_details['longitude'] ?>
      //       },
      //       zoom: 13,
      //       mapId: "4504f8b37365c3d0",
      //    });
      //    dropOffDirectionsRenderer.setMap(map);

      // }

      var dropoffLongitude = <?php echo @$company_details['longitude'] ?>;
      var dropoffLatitude = <?php echo @$company_details['latitude'] ?>;
      var pickupAddressLongitude = <?php echo @$company_details['longitude'] ?>;
      var pickupAddressLatitude = <?php echo @$company_details['latitude'] ?>;

      var waypts = [];

      for (let value of Object.values(wayPointArr)) {
         waypts.push({
            location: new google.maps.LatLng(value[0], value[1]),
            stopover: true,
         });
      }

      try {

         directionsService
            .route({
               origin: dropoffLatitude + "," + dropoffLongitude,
               destination: dropoffLatitude + "," + dropoffLongitude,
               waypoints: waypts,
               optimizeWaypoints: type,
               travelMode: google.maps.TravelMode.DRIVING,
            })
            .then((response) => {
               waypts = [];
               const route = response.routes[0];
               for (let i = 0; i < route.legs.length - 1; i++) {
                  if (type == true) {
                     if (i == 0) {
                        pickupAddressLongitude = route.legs[i].end_location.lng();
                        pickupAddressLatitude = route.legs[i].end_location.lat();
                     } else {
                        waypts.push({
                           location: new google.maps.LatLng(route.legs[i].end_location.lat(), route.legs[i].end_location.lng()),
                           stopover: true,
                        });
                     }
                  } else {
                     if (i != (route.legs.length - 2)) {
                        pickupAddressLongitude = dropoffLongitude;
                        pickupAddressLatitude = dropoffLatitude;
                        waypts.push({
                           location: new google.maps.LatLng(route.legs[i].end_location.lat(), route.legs[i].end_location.lng()),
                           stopover: true,
                        });
                     } else {
                        dropoffLongitude = route.legs[i].end_location.lng();
                        dropoffLatitude = route.legs[i].end_location.lat();
                     }
                  }
               }
               if (type == true) {
                  displayUpdatedRoute(dropoffLatitude, dropoffLongitude, pickupAddressLatitude, pickupAddressLongitude, waypts, type, pickUpDirectionsRenderer);
               } else {
                  displayUpdatedRoute(dropoffLatitude, dropoffLongitude, pickupAddressLatitude, pickupAddressLongitude, waypts, type, dropOffDirectionsRenderer);
               }
            })
            .catch((e) => console.log("Directions request failed due to " + e));
      } catch (err) {
         console.log("Directions request failed due to " + err.message)
      }
   }

   function displayUpdatedRoute(dropoffLatitude, dropoffLongitude, pickupAddressLatitude, pickupAddressLongitude, waypts, type, renderer) {

      directionsService
         .route({
            origin: pickupAddressLatitude + "," + pickupAddressLongitude,
            destination: dropoffLatitude + "," + dropoffLongitude,
            waypoints: waypts,
            optimizeWaypoints: type,
            travelMode: google.maps.TravelMode.DRIVING,
         })
         .then((response) => {

            renderer.setDirections(response);

            const route = response.routes[0];
            var summaryPanel = document.getElementById("pickUpDirectionsPanel");
            if (type == true) {
               summaryPanel = document.getElementById("pickUpDirectionsPanel");
            } else {
               summaryPanel = document.getElementById("dropOffDirectionsPanel");
            }

            summaryPanel.innerHTML = "";

            // For each route, display summary information.
            for (let i = 0; i < route.legs.length; i++) {
               const routeSegment = i + 1;

               summaryPanel.innerHTML +=
                  "<b>Route Segment: " + routeSegment + "</b><br>";
               summaryPanel.innerHTML += route.legs[i].start_address + " to ";
               summaryPanel.innerHTML += route.legs[i].end_address + "<br>";
               summaryPanel.innerHTML += route.legs[i].distance.text + "<br><br>";
            }
         })
         .catch((e) => window.alert("222Directions request failed due to " + status));

   }

   window.calculateAndDisplayRoute = calculateAndDisplayRoute;
</script>

<script type="text/javascript">
   $(document).ready(function() {

      $('.js-example-basic-single').select2();

      $("#datepicker").datepicker({
         maxDate: 0,
         dateFormat: 'dd-mm-yy'
      });

   });

   var client_array = [];
   var pick_up_carer_array = [];
   var pick_up_waypoint_array = [];

   var drop_off_carer_array = [];
   var drop_off_waypoint_array = [];

   $(document).ready(function() {
      $(".JobBoard").css('display', 'none');
      $(".repeated").css('display', 'none');

      pick_up_carer_array = $('#pickUpCarerSelect').val();
      drop_off_carer_array = $('#dropOffCarerSelect').val();

      $.each(pick_up_carer_array, function(index, value) {
         var latitude = $("#latitude_" + value).val();
         var longitude = $("#longitude_" + value).val();

         var temparr = [];
         temparr.push(latitude, longitude);
         pick_up_waypoint_array[value] = temparr;
      });

      $.each(drop_off_carer_array, function(index, value) {
         var latitude = $("#latitude_" + value).val();
         var longitude = $("#longitude_" + value).val();

         var temparr = [];
         temparr.push(latitude, longitude);
         drop_off_waypoint_array[value] = temparr;
      });

      calculateAndDisplayRoute(pick_up_waypoint_array, true);
      calculateAndDisplayRoute(drop_off_waypoint_array, false);

      $("#reacurrance").change(function() {
         reacurranceVisible();
      });

      $(".pickUpCarerSelect").change(function() {
         var current_array = $(this).val();
         var add_diff = current_array.filter(x => pick_up_carer_array.indexOf(x) === -1);
         var remove_diff = pick_up_carer_array.filter(x => current_array.indexOf(x) === -1);

         if (add_diff.length != 0) {

            var latitude = $("#latitude_" + add_diff[0]).val();
            var longitude = $("#longitude_" + add_diff[0]).val();

            var temparr = [];
            temparr.push(latitude, longitude);
            pick_up_waypoint_array[add_diff[0]] = temparr;
            // if ($(".carerTimes").data("holiday") == "yes") {
            //    var t1 = '<div class="row" id="carerTime' + add_diff[0] + '"><div class="col-sm-6"><input type="hidden" name="carerTimes[' + add_diff[0] + '][carer_id]" value="' + add_diff[0] + '"><label for="exampleInputName1">Make this day working <code>*</code></label></div><div class="col-sm-6"><input type="checkbox" name="carerTimes[' + add_diff[0] + '][make_working]" id="make_working_' + add_diff[0] + '"></div></div>'; //<div class="col-12 mt-2"><h6>Carer Name</h6><input type="hidden" name="carerTimes[' + add_diff[0] + '][carer_id]" value="' + add_diff[0] + '"></div><div class="col-sm-6 mt-2 role"><label for="exampleInputName1">Start Time <code>*</code></label><input type="time" name="carerTimes[' + add_diff[0] + '][start_time]" class="form-control" placeholder="Date"></div><div class="col-sm-6 mt-2 role"><label for="exampleInputName1">End Time <code>*</code></label><input type="time" name="carerTimes[' + add_diff[0] + '][end_time]" class="form-control" placeholder="Date"></div><div class="col-sm-12 mt-2 role"><label for="exampleInputName1">Paygroups <code>*</code></label><select class="form-control js-example-basic-single" name="carerTimes[' + add_diff[0] + '][paygroup_id]" required=""><option disabled="">Select</option>@foreach($paygroups as $paygroup)<option value="{{$paygroup->id}}">{{$paygroup->name}}</option>@endforeach</select></div></div>';
            // } else {
            //    var t1 = '<div class="row" id="carerTime' + add_diff[0] + '"><input type="hidden" name="carerTimes[' + add_diff[0] + '][carer_id]" value="' + add_diff[0] + '"></div>';
            // }
            var t1 = '<div class="row" id="pickUpCarerTime' + add_diff[0] + '"><input type="hidden" name="pickUpCarerTimes[' + add_diff[0] + '][carer_id]" value="' + add_diff[0] + '"></div>';
            $('.pickUpCarerTimes').append(t1);
         } else if (remove_diff.length != 0) {
            delete pick_up_waypoint_array[remove_diff[0]];
            $("#pickUpCarerTime" + remove_diff[0]).remove();
         }
         calculateAndDisplayRoute(pick_up_waypoint_array, true);
         pick_up_carer_array = current_array;
         const selectedCapacity = parseInt($('#vehicleSelect option:selected').data('capacity'));
         // if (carer_array.length == selectedCapacity) {
         //    $(".carerSelect").prop("disabled", true);
         // }
         // else if(carer_array.length > selectedCapacity){
         //    $(".carerSelect").removeAttr("selected");
         // }
      });

      $(".dropOffCarerSelect").change(function() {
         var current_array = $(this).val();
         var add_diff = current_array.filter(x => drop_off_carer_array.indexOf(x) === -1);
         var remove_diff = drop_off_carer_array.filter(x => current_array.indexOf(x) === -1);

         if (add_diff.length != 0) {

            var latitude = $("#latitude_" + add_diff[0]).val();
            var longitude = $("#longitude_" + add_diff[0]).val();

            var temparr = [];
            temparr.push(latitude, longitude);
            drop_off_waypoint_array[add_diff[0]] = temparr;
            // if ($(".carerTimes").data("holiday") == "yes") {
            //    var t1 = '<div class="row" id="carerTime' + add_diff[0] + '"><div class="col-sm-6"><input type="hidden" name="carerTimes[' + add_diff[0] + '][carer_id]" value="' + add_diff[0] + '"><label for="exampleInputName1">Make this day working <code>*</code></label></div><div class="col-sm-6"><input type="checkbox" name="carerTimes[' + add_diff[0] + '][make_working]" id="make_working_' + add_diff[0] + '"></div></div>'; //<div class="col-12 mt-2"><h6>Carer Name</h6><input type="hidden" name="carerTimes[' + add_diff[0] + '][carer_id]" value="' + add_diff[0] + '"></div><div class="col-sm-6 mt-2 role"><label for="exampleInputName1">Start Time <code>*</code></label><input type="time" name="carerTimes[' + add_diff[0] + '][start_time]" class="form-control" placeholder="Date"></div><div class="col-sm-6 mt-2 role"><label for="exampleInputName1">End Time <code>*</code></label><input type="time" name="carerTimes[' + add_diff[0] + '][end_time]" class="form-control" placeholder="Date"></div><div class="col-sm-12 mt-2 role"><label for="exampleInputName1">Paygroups <code>*</code></label><select class="form-control js-example-basic-single" name="carerTimes[' + add_diff[0] + '][paygroup_id]" required=""><option disabled="">Select</option>@foreach($paygroups as $paygroup)<option value="{{$paygroup->id}}">{{$paygroup->name}}</option>@endforeach</select></div></div>';
            // } else {
            //    var t1 = '<div class="row" id="carerTime' + add_diff[0] + '"><input type="hidden" name="carerTimes[' + add_diff[0] + '][carer_id]" value="' + add_diff[0] + '"></div>';
            // }
            var t1 = '<div class="row" id="dropOffCarerTime' + add_diff[0] + '"><input type="hidden" name="dropOffCarerTimes[' + add_diff[0] + '][carer_id]" value="' + add_diff[0] + '"></div>';
            $('.dropOffCarerTimes').append(t1);
         } else if (remove_diff.length != 0) {
            delete drop_off_waypoint_array[remove_diff[0]];
            $("#dropOffCarerTime" + remove_diff[0]).remove();
         }
         calculateAndDisplayRoute(drop_off_waypoint_array, false);
         drop_off_carer_array = current_array;
         const selectedCapacity = parseInt($('#vehicleSelect option:selected').data('capacity'));
         // if (carer_array.length == selectedCapacity) {
         //    $(".carerSelect").prop("disabled", true);
         // }
         // else if(carer_array.length > selectedCapacity){
         //    $(".carerSelect").removeAttr("selected");
         // }
      });

      $("#add_to_job_board").change(function() {
         if (this.checked) {
            $(".NotJobBoard").css('display', 'none');
            $(".JobBoard").css('display', 'block');
         } else {
            $(".NotJobBoard").css('display', 'block');
            $(".JobBoard").css('display', 'none');
         }
      });

      $("#is_repeat").change(function() {
         if (this.checked) {
            $(".repeated").css('display', 'block');
            reacurranceVisible();
         } else {
            $(".repeated").css('display', 'none');
         }
      });


      $(".role.DropOffAddress").css('display', 'block');

      function reacurranceVisible() {
         var current = $("#reacurrance").val();
         if (current == "daily") {
            $(".Daily").css('display', 'block');
            $(".Weekly").css('display', 'none');
            $(".Monthly").css('display', 'none');
         } else if (current == "weekly") {
            $(".Weekly").css('display', 'block');
            $(".Daily").css('display', 'none');
            $(".Monthly").css('display', 'none');
         } else if (current == "monthly") {
            $(".Monthly").css('display', 'block');
            $(".Weekly").css('display', 'none');
            $(".Daily").css('display', 'none');
         }
      }

      var task_id = 1;
      $(".addTask").click(function() {
         if ($('#task_name').val() != "") {
            var task_name = $('#task_name').val();

            var is_mandatory = document.getElementById('is_mandatory');
            if (is_mandatory.checked) {
               var is_mandatory_val = 'on';
            } else {
               var is_mandatory_val = 'off';
            }

            var t1 = '<tr><td><input type="hidden" value="' + task_name + '" name="tasks[' + task_id + '][name]" /><input type="hidden" value="' + is_mandatory_val + '" name="tasks[' + task_id + '][is_mandatory]" />' + task_name + '</td><td>' + is_mandatory_val + '</td><td>@mdo</td></tr>';
            $('.tasks').append(t1);
            task_id++;
         }
      });

   });
</script>

@endsection