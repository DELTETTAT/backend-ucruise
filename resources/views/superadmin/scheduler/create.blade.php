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

   .border-red {
      border-color: red !important;
   }

   .border-green {
      border-color: green !important;
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
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Add Schedule</a></li>
               </ol>
            </div>
            <h4 class="page-title">Add Schedule</h4>
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

         <form method="POST" action="{{ route('storeSchedule') }}" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="card-body">
               <div class="form-group">
                  <div class="row">

                     <div class="col-sm-6 mt-2 role ">
                        <div class="row">
                           <div class="col-sm-6 mt-3 role">
                              <label for="exampleInputName1">Search location<code>*</code></label>


                           </div>
                           <div class="col-sm-6 mt-3 role">
                              <input type="text" id="scheduleLocation" name="scheduleLocation" class="form-control">
                              <input type="hidden" id="selectedLocationLat" name="selectedLocationLat">
                              <input type="hidden" id="selectedLocationLng" name="selectedLocationLng">
                           </div>
                        </div>

                        <div class="row">
                           <div class="col-sm-6 mt-3 role">
                              <label for="exampleInputName1">City<code>*</code></label>

                           </div>
                           <div class="col-sm-6 mt-3 role">
                              <input type="text" id="scheduleCity" name="scheduleCity" class="form-control" placeholder="City" readonly>

                           </div>
                        </div>

                        

                        <!-- time and location -->

                        <div class="row mt-2">

                           <div class="col-sm-12 mt-3 role">
                              <label for="exampleInputName1">Driver <code>*</code></label>
                              <select class="form-control js-example-basic-single" name="driver" required>
                                 <option disabled="">Select</option>
                                 @foreach($drivers as $driver)
                                 <option value="{{$driver->driver_id}}" {{ old('driver') == $driver->driver_id ? 'selected' : '' }}>{{$driver->first_name . ' ' . $driver->last_name}}
                                    &nbsp;&nbsp; &nbsp;&nbsp;
                                    {{$driver->name}} ({{$driver->seats}} seats - ₹ {{$driver->fare}})
                                 </option>
                                 @endforeach
                              </select>
                           </div>
                           <!-- <div class="col-sm-6 mt-3 role">
                              <label for="exampleInputName1">Vehicle <code>*</code></label>
                              <select class="form-control js-example-basic-single" id="vehicleSelect" name="vehicle" required>
                                 <option disabled="">Select</option>
                                 @foreach($vehicles as $vehicle)
                                 <option value="{{$vehicle->id}}" data-capacity="{{$vehicle->seats}}" {{ old('vehicle') == $vehicle->id ? 'selected' : '' }}>{{$vehicle->name}}
                                    ({{$vehicle->seats}} seats - ₹ {{$vehicle->fare}})
                                 </option>
                                 @endforeach
                              </select>
                           </div> -->

                           <div class="col-sm-6 mt-3 role">
                              <label for="exampleInputName1">Date <code>*</code></label>
                           </div>

                           <div class="col-sm-6 mt-3 role text-right">
                              <input type="date" name="date" class="form-control" placeholder="Date" value="{{old('date')}}">
                           </div>

                           <div class="col-sm-6 mt-3 role">
                              <label for="exampleInputName1">Shift Finishes Next Day <code>*</code></label>
                           </div>

                           <div class="col-sm-6 mt-3 role text-right">
                              <input type="checkbox" name="shift_finishes_next_day" id="select-all-checkbox" {{ old('shift_finishes_next_day') ? 'checked' : '' }}>
                           </div>



                           <div class="col-sm-6 mt-3 role">
                              <label for="exampleInputName1">Break time in minutes <code>*</code></label>
                           </div>

                           <div class="col-sm-6 mt-3 role text-right">
                              <input type="number" name="break_time_in_minutes" class="form-control" placeholder="Enter Minutes" value="{{ old('break_time_in_minutes') }}">
                           </div>

                           <div class="col-sm-6 mt-3 role">
                              <label for="exampleInputName1">Repeat <code>*</code></label>

                           </div>

                           <div class="col-sm-6 mt-3 role text-right">
                              <input type="checkbox" name="is_repeat" id="is_repeat">
                           </div>

                           <div class="col-sm-12 mt-3 role repeated">
                              <label for="exampleInputName1">Reacurrance <code>*</code></label>
                              <select class="form-control js-example-basic-single" name="reacurrance" id="reacurrance">
                                 <option value="daily">Daily</option>
                                 <option value="weekly">Weekly</option>
                                 <option value="monthly">Monthly</option>
                              </select>
                           </div>

                           <div class="col-sm-6 mt-3 role Daily repeated">
                              <label for="exampleInputName1">Repeat every (Days)<code>*</code></label>
                              <select class="form-control js-example-basic-single" name="repeat_days">
                                 <option value="1">1</option>
                                 <option value="2">2</option>
                                 <option value="3">3</option>
                                 <option value="4">4</option>
                                 <option value="5">5</option>
                                 <option value="6">6</option>
                                 <option value="7">7</option>
                                 <option value="8">8</option>
                                 <option value="9">9</option>
                                 <option value="10">10</option>
                                 <option value="11">11</option>
                                 <option value="12">12</option>
                                 <option value="13">13</option>
                                 <option value="14">14</option>
                                 <option value="15">15</option>
                              </select>
                           </div>

                           <div class="col-sm-6 mt-3 role Weekly repeated">
                              <label for="exampleInputName1">Repeat every (Week)<code>*</code></label>
                              <select class="form-control js-example-basic-single" name="repeat_weeks">
                                 <option value="1">1</option>
                                 <option value="2">2</option>
                                 <option value="3">3</option>
                                 <option value="4">4</option>
                                 <option value="5">5</option>
                                 <option value="6">6</option>
                                 <option value="7">7</option>
                                 <option value="8">8</option>
                                 <option value="9">9</option>
                                 <option value="10">10</option>
                                 <option value="11">11</option>
                                 <option value="12">12</option>
                              </select>
                           </div>

                           <div class="col-sm-6 mt-3 role Monthly repeated">
                              <label for="exampleInputName1">Repeat every (Month)<code>*</code></label>
                              <select class="form-control js-example-basic-single" name="repeat_months">
                                 <option value="1">1</option>
                                 <option value="2">2</option>
                                 <option value="3">3</option>
                              </select>
                           </div>

                           <div class="col-sm-12 mt-3 role Monthly repeated">
                              <label for="exampleInputName1">Repeat every (Day of the Month)<code>*</code></label>
                              <select class="form-control js-example-basic-single" name="repeat_day_of_month">
                                 <option value="1">1</option>
                                 <option value="2">2</option>
                                 <option value="3">3</option>
                                 <option value="4">4</option>
                                 <option value="5">5</option>
                                 <option value="6">6</option>
                                 <option value="7">7</option>
                                 <option value="8">8</option>
                                 <option value="9">9</option>
                                 <option value="10">10</option>
                                 <option value="11">11</option>
                                 <option value="12">12</option>
                                 <option value="13">13</option>
                                 <option value="14">14</option>
                                 <option value="15">15</option>
                                 <option value="16">16</option>
                                 <option value="17">17</option>
                                 <option value="18">18</option>
                                 <option value="19">19</option>
                                 <option value="20">20</option>
                                 <option value="21">21</option>
                                 <option value="22">22</option>
                                 <option value="23">23</option>
                                 <option value="24">24</option>
                                 <option value="25">25</option>
                                 <option value="26">26</option>
                                 <option value="27">27</option>
                                 <option value="28">28</option>
                                 <option value="29">29</option>
                                 <option value="30">30</option>
                                 <option value="31">31</option>
                              </select>
                           </div>

                           <div class="col-sm-12 mt-3 role Weekly repeated">
                              <label for="exampleInputName1">Mon <code>*</code></label>
                              <input type="checkbox" name="mon" id="mon">

                              <label for="exampleInputName1" class="ml-2">Tue <code>*</code></label>
                              <input type="checkbox" name="tue" id="tue">

                              <label for="exampleInputName1" class="ml-2">Wed <code>*</code></label>
                              <input type="checkbox" name="wed" id="wed">

                              <label for="exampleInputName1" class="ml-2">Thu <code>*</code></label>
                              <input type="checkbox" name="thu" id="thu">

                              <label for="exampleInputName1" class="ml-2">Fri <code>*</code></label>
                              <input type="checkbox" name="fri" id="fri">

                              <label for="exampleInputName1" class="ml-2">Sat <code>*</code></label>
                              <input type="checkbox" name="sat" id="sat">

                              <label for="exampleInputName1" class="ml-2">Sun <code>*</code></label>
                              <input type="checkbox" name="sun" id="sun">
                           </div>

                           <div class="col-sm-6 mt-3 role repeated">
                              <label for="exampleInputName1">End Date <code>*</code></label>
                              <input type="date" name="reacurrance_end_time" class="form-control" placeholder="End Time">
                           </div>


                           <!-- Shift -->

                           <div class="col-sm-6 mt-3 role">
                              <label for="exampleInputName1">Shift Type <code>*</code></label>
                              <select class="form-control js-example-basic-single" name="shift_types" id="shiftType" required>
                                 <option disabled="">Select</option>
                                 @foreach($shiftTypes as $shiftType)
                                 <option value="{{$shiftType->id}}">{{$shiftType->name}}</option>
                                 @endforeach
                              </select>
                           </div>

                           <div class="col-sm-6 mt-3 role">
                              <label for="exampleInputName1">Pricebook<code>*</code></label>
                              <select class="form-control js-example-basic-single" name="pricebook" required>
                                 <option disabled="">Select</option>
                                 @foreach($pricebooks as $pricebook)
                                 <option value="{{$pricebook->id}}" {{ old('pribook') == $pricebook->id ? 'selected' : '' }}>{{$pricebook->name}}</option>
                                 @endforeach
                              </select>
                           </div>


                           <div class="col-sm-3 mt-3 role">
                              <label for="exampleInputName1">Ignore Staff Count <code>*</code></label>
                           </div>

                           <div class="col-sm-3 mt-3 role">
                              <input type="checkbox" name="ignore_staff_count" id="select-all-checkbox" {{ old('ignore_staff_count') ? 'checked' : '' }}>
                           </div>

                           <div class="col-sm-3 mt-3 role">
                              <label for="exampleInputName1">Confirmation Required <code>*</code></label>
                           </div>

                           <div class="col-sm-3 mt-3 role">
                              <input type="checkbox" name="confirmation_required" id="select-all-checkbox" {{ old('confirmation_required') ? 'checked' : '' }}>
                           </div>

                           <div class="col-12">
                              <div class="row">

                                 @foreach($carers as $carer)
                                 <input type="hidden" name="longitude" id="longitude_{{$carer->id}}" value="{{$carer->longitude}}">
                                 <input type="hidden" name="latitude" id="latitude_{{$carer->id}}" value="{{$carer->latitude}}">
                                 <input type="hidden" name="first_name" id="first_{{$carer->id}}" value="{{$carer->first_name}}">
                                 <input type="hidden" name="gender" id="gender_{{$carer->id}}" value="{{$carer->gender}}">
                                 @endforeach

                                 <div class="col-sm-6 mt-3 role" id="pickupTime">
                                    <label for="exampleInputName1">PickUp time <code>*</code></label>
                                    <input type="time" name="start_time" class="form-control" placeholder="Date">

                                    <div class="row">
                                       <div class="col-12 mt-1 role NotJobBoard">
                                          <label for="exampleInputName1">Pick Up Staffs <code>*</code></label>
                                          <select class="form-control js-example-basic-single pickUpCarerSelect" multiple="multiple" name="pickUpCarer[]">
                                             <option disabled="">Select</option>
                                             @foreach($carers as $carer)
                                             <option value="{{$carer->id}}">{{$carer->first_name}}</option>
                                             @endforeach
                                          </select>
                                       </div>

                                       <div class="col-sm-12 mt-3 role">
                                          <div id="pickUpMap"></div>
                                          <div id="pickUpDirectionsPanel"></div>
                                       </div>

                                       <div class="col-sm-6 mt-3 role NotJobBoard">
                                          <label for="exampleInputName1">Notify Carer <code>*</code></label>
                                       </div>

                                       <div class="col-sm-6 mt-3 role NotJobBoard">
                                          <input type="checkbox" name="notify_carer" id="select-all-checkbox">
                                       </div>
                                    </div>

                                 </div>

                                 <div class="col-sm-6 mt-3 role" id="dropoffTime">
                                    <label for="exampleInputName1">DropOff Time <code>*</code></label>
                                    <input type="time" name="end_time" class="form-control" placeholder="Date">

                                    <div class="row">
                                       <div class="col-12 mt-1 role NotJobBoard">
                                          <label for="exampleInputName1">Drop Off Staffs <code>*</code></label>
                                          <select class="form-control js-example-basic-single dropOffCarerSelect" multiple="multiple" name="dropOffCarer[]">
                                             <option disabled="">Select</option>
                                             @foreach($carers as $carer)
                                             <option value="{{$carer->id}}">{{$carer->first_name}}</option>
                                             @endforeach
                                          </select>
                                       </div>

                                       <div class="col-sm-12 mt-3 role">
                                          <div id="dropOffMap"></div>
                                          <div id="dropOffDirectionsPanel"></div>
                                       </div>

                                       <div class="col-sm-6 mt-3 role NotJobBoard">
                                          <label for="exampleInputName1">Notify Carer <code>*</code></label>
                                       </div>

                                       <div class="col-sm-6 mt-3 role NotJobBoard">
                                          <input type="checkbox" name="notify_carer" id="select-all-checkbox">
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
                           <div class="col-sm-12 mt-3 role">
                              <div class="row">
                                 <div class="col-sm-3">
                                    <h5>Tasks</h5>
                                 </div>
                                 <div class="col-sm-4">
                                    <input type="text" name="task_name" id="task_name" class="form-control" placeholder="Add Task" value="{{old('task_name')}}">
                                 </div>
                                 <div class="col-sm-3">
                                    <label for="exampleInputName1">is Mandatory <code>*</code></label>
                                    <input type="checkbox" name="is_mandatory" id="is_mandatory" {{old('is_mandatory') ? 'checked' : '' }}>
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
                                          @foreach(old('tasks', []) as $task)
                                          <tr>
                                             <td>{{ $task['name'] }}</td>
                                             <td>{{ $task['is_mandatory'] ? 'on' : 'off' }}</td>
                                             <td>
                                                <!-- Add your remove button/link here -->
                                                <a href="javascript:void(0)" class="btn btn-danger removeTask">Remove</a>
                                             </td>
                                          </tr>
                                          @endforeach
                                       </tbody>
                                    </table>
                                 </div>
                              </div>
                           </div>
                        </div>

                        <label for="exampleInputName1">Instructions <code>*</code></label>
                        <textarea name="instructions" class="form-control" cols="30" rows="10">{{ old('instructions') }}</textarea>

                     </div>

                  </div>
               </div>
            </div>
      </div>


      <div class="card-footer">
         <button type="submit" class="btn btn-success" value="1" name="exit">Save</button>

         <a href="javascript:;" class="btn btn-danger" onclick="history.back()">Back</a>
      </div>
      </form>
   </div>
   <!-- end col -->
</div>
<!-- end row -->
</div> <!-- container -->
@endsection
@section('script')

<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
   const GOOGLE_API_KEY = '{{ env("GOOGLE_API_KEY") }}';
</script>

<script>
   var script = document.createElement('script');
   script.src = "https://maps.googleapis.com/maps/api/js?key=" + GOOGLE_API_KEY + "&libraries=places&callback=initMap&v=weekly";
   script.defer = true;
   document.head.appendChild(script);
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
   $(document).ready(function() {

      $('#dropoffTime').hide();

      $('#shiftType').change(function() {
         var selectedShiftType = $(this).val();

         if (selectedShiftType == 1) {
            $('#dropoffTime').hide();
            $('#pickupTime').show();
            calculateAndDisplayRoute(pick_up_waypoint_array, pickUpDirectionsRenderer, true);
         } else if (selectedShiftType == 2) {
            $('#dropoffTime').show();
            $('#pickupTime').show();
            calculateAndDisplayRoute(pick_up_waypoint_array, pickUpDirectionsRenderer, true);
            console.log("drop now");
            calculateAndDisplayRoute(drop_off_waypoint_array, dropOffDirectionsRenderer, false);
         } else {
            $('#dropoffTime').show();
            $('#pickupTime').hide();
            calculateAndDisplayRoute(drop_off_waypoint_array, dropOffDirectionsRenderer, false);
         }
      });

   });
</script>
<script>
   var scheduleCity;

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

      calculateAndDisplayRoute(pick_up_waypoint_array, pickUpDirectionsRenderer, true);
      calculateAndDisplayRoute(drop_off_waypoint_array, dropOffDirectionsRenderer, false);


      // New code added 
      // Declare a variable to store the selected city
      const scheduleCityInput = document.getElementById("scheduleCity");
      const scheduleCityLatInput = document.getElementById("scheduleCityLat");
      const scheduleCityLngInput = document.getElementById("scheduleCityLng");
      const selectedLocationLatInput = document.getElementById("selectedLocationLat");
      const selectedLocationLngInput = document.getElementById("selectedLocationLng");
      const scheduleLocationInput = document.getElementById("scheduleLocation");
      const scheduleLocationAutocomplete = new google.maps.places.Autocomplete(scheduleLocationInput, {
         //types: ['geocode']
      });

      scheduleLocationAutocomplete.addListener('place_changed', function() {
         const place = scheduleLocationAutocomplete.getPlace();

         if (place && place.address_components) {
            const cityComponent = place.address_components.find(component => {
               return component.types.includes('locality');
            });

            scheduleCity = cityComponent ? cityComponent.long_name : '';
            scheduleCityInput.value = scheduleCity;

            const latitude = place.geometry.location.lat();
            const longitude = place.geometry.location.lng();



            // Save latitude and longitude in hidden input fields
            selectedLocationLatInput.value = latitude;

            selectedLocationLngInput.value = longitude;
         }
      });

   }




   function calculateAndDisplayRoute(waypoints, renderer, type) {

      var dropoffLongitude = <?php echo @$company_details['longitude'] ?>;
      var dropoffLatitude = <?php echo @$company_details['latitude'] ?>;
      var pickupAddressLongitude = <?php echo @$company_details['longitude'] ?>;
      var pickupAddressLatitude = <?php echo @$company_details['latitude'] ?>;

      var waypts = [];


      for (let value of Object.values(waypoints)) {
         waypts.push({
            location: new google.maps.LatLng(value[0], value[1]),
            stopover: true,
         });
      }

      // map_type_selected = $("input[name='map_type']:checked").val();

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

            return displayUpdatedRoute(dropoffLatitude, dropoffLongitude, pickupAddressLatitude, pickupAddressLongitude, waypts, renderer, type);

         })
         .catch((e) => console.log("Directions request failed due to " + e));
   }

   function displayUpdatedRoute(dropoffLatitude, dropoffLongitude, pickupAddressLatitude, pickupAddressLongitude, waypts, renderer, optimize) {
      directionsService
         .route({
            origin: pickupAddressLatitude + "," + pickupAddressLongitude,
            destination: dropoffLatitude + "," + dropoffLongitude,
            waypoints: waypts,
            optimizeWaypoints: optimize,
            travelMode: google.maps.TravelMode.DRIVING,
         })
         .then((response) => {

            renderer.setDirections(response);

            const route = response.routes[0];
            var summaryPanel = document.getElementById("pickUpDirectionsPanel");
            if (optimize == true) {
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

   window.initMap = initMap;
</script>
<script type="text/javascript">
   $(document).ready(function() {

      $('.js-example-basic-single').select2();

      $("#datepicker").datepicker({
         maxDate: 0,
         dateFormat: 'dd-mm-yy'
      });

   });
   var pickupCarerCity
   var client_array = [];
   var pick_up_carer_array = [];
   var pick_up_waypoint_array = [];
   var drop_off_carer_array = [];
   var drop_off_waypoint_array = [];

   $(document).ready(function() {
      $(".JobBoard").css('display', 'none');
      $(".DropOffAddress").css('display', 'none');
      $(".repeated").css('display', 'none');

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
            var first_name = $("#first_" + add_diff[0]).val();



            isCarerInRadius(latitude, longitude, function(isWithinRadius) {
               console.log("Is carer within radius?", isWithinRadius);
               var selectedChoice = $('.select2-selection__rendered').find('.select2-selection__choice[title*="' + first_name + '"]');
               //var selectedChoice = $('.pickUpCarerSelect').next().find('.select2-selection__choice[title*="' +first_name + '"]');
               //console.log($selectedChoice);
               console.log(selectedChoice);
               if (!isWithinRadius) {
                  selectedChoice.addClass('border-red');
               } else {
                  selectedChoice.addClass('border-green');
               }
            });



            var temparr = [];
            temparr.push(latitude, longitude);
            pick_up_waypoint_array[add_diff[0]] = temparr
            var t1 = '<div class="row" id="carerTime' + add_diff[0] + '"></div>'; //<div class="col-12 mt-2"><h6>Carer Name</h6><input type="hidden" name="carerTimes[' + add_diff[0] + '][carer_id]" value="' + add_diff[0] + '"></div><div class="col-sm-6 mt-2 role"><label for="exampleInputName1">Start Time <code>*</code></label><input type="time" name="carerTimes[' + add_diff[0] + '][start_time]" class="form-control" placeholder="Date"></div><div class="col-sm-6 mt-2 role"><label for="exampleInputName1">End Time <code>*</code></label><input type="time" name="carerTimes[' + add_diff[0] + '][end_time]" class="form-control" placeholder="Date"></div><div class="col-sm-12 mt-2 role"><label for="exampleInputName1">Paygroups <code>*</code></label><select class="form-control js-example-basic-single" name="carerTimes[' + add_diff[0] + '][paygroup_id]" required=""><option disabled="">Select</option>@foreach($paygroups as $paygroup)<option value="{{$paygroup->id}}">{{$paygroup->name}}</option>@endforeach</select></div></div>';

            // $('.carerTimes').append(t1);
         } else if (remove_diff.length != 0) {
            $('.pickUpCarerSelect').siblings('.select2-container').removeClass('border-red');
            delete pick_up_waypoint_array[remove_diff[0]];
            //$("#carerTime" + remove_diff[0]).remove();
         }
         calculateAndDisplayRoute(pick_up_waypoint_array, pickUpDirectionsRenderer, true);
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

            isCarerInRadius(latitude, longitude, function(isWithinRadius) {
               console.log("Is carer within radius?", isWithinRadius);

               if (!isWithinRadius) {
                  // Carer is outside the radius, handle accordingly
                  $('.dropOffCarerSelect').siblings('.select2-container').addClass('border-red');
               } else {
                  $('.dropOffCarerSelect').siblings('.select2-container').removeClass('border-red');
               }
            });

            var temparr = [];
            temparr.push(latitude, longitude);
            drop_off_waypoint_array[add_diff[0]] = temparr;
            var t1 = '<div class="row" id="carerTime' + add_diff[0] + '"></div>'; //<div class="col-12 mt-2"><h6>Carer Name</h6><input type="hidden" name="carerTimes[' + add_diff[0] + '][carer_id]" value="' + add_diff[0] + '"></div><div class="col-sm-6 mt-2 role"><label for="exampleInputName1">Start Time <code>*</code></label><input type="time" name="carerTimes[' + add_diff[0] + '][start_time]" class="form-control" placeholder="Date"></div><div class="col-sm-6 mt-2 role"><label for="exampleInputName1">End Time <code>*</code></label><input type="time" name="carerTimes[' + add_diff[0] + '][end_time]" class="form-control" placeholder="Date"></div><div class="col-sm-12 mt-2 role"><label for="exampleInputName1">Paygroups <code>*</code></label><select class="form-control js-example-basic-single" name="carerTimes[' + add_diff[0] + '][paygroup_id]" required=""><option disabled="">Select</option>@foreach($paygroups as $paygroup)<option value="{{$paygroup->id}}">{{$paygroup->name}}</option>@endforeach</select></div></div>';

            // $('.carerTimes').append(t1);
         } else if (remove_diff.length != 0) {
            $('.dropOffCarerSelect').siblings('.select2-container').removeClass('border-red');
            delete drop_off_waypoint_array[remove_diff[0]];
            //$("#carerTime" + remove_diff[0]).remove();
         }
         calculateAndDisplayRoute(drop_off_waypoint_array, dropOffDirectionsRenderer, false);
         drop_off_carer_array = current_array;
         const selectedCapacity = parseInt($('#vehicleSelect option:selected').data('capacity'));
         // if (carer_array.length == selectedCapacity) {
         //    $(".carerSelect").prop("disabled", true);
         // }
         // else if(carer_array.length > selectedCapacity){
         //    $(".carerSelect").removeAttr("selected");
         // }
      });

      function isCarerInRadius(carerLatitude, carerLongitude, callback) {

         var scheduleLocationLatitude = parseFloat($('#selectedLocationLat').val());
         var scheduleLocationLongitude = parseFloat($('#selectedLocationLng').val());
         var radius = 5;
         var carerLatLng = new google.maps.LatLng(carerLatitude, carerLongitude);
         var scheduleLocationLatLng = new google.maps.LatLng(scheduleLocationLatitude, scheduleLocationLongitude);
         var distanceMatrixService = new google.maps.DistanceMatrixService();
         distanceMatrixService.getDistanceMatrix({
            origins: [carerLatLng],
            destinations: [scheduleLocationLatLng],
            travelMode: google.maps.TravelMode.DRIVING,
            unitSystem: google.maps.UnitSystem.METRIC
         }, function(response, status) {
            if (status === google.maps.DistanceMatrixStatus.OK) {
               // Check if the distance is within the radius
               var distanceInKm = response.rows[0].elements[0].distance.value / 1000;
               console.log(distanceInKm)
               var isWithinRadius = distanceInKm <= radius;
               callback(isWithinRadius);
            } else {
               // Handle error
               console.error('Distance Matrix request failed: ' + status);
               callback(false);
            }
         });
      }
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
      $(".tasks").on("click", ".removeTask", function() {
         $(this).closest("tr").remove();
      });

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