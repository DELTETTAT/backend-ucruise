@extends('layouts.vertical', ['title' => 'Add Client'])
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
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Clients</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Add Client</a></li>
               </ol>
            </div>
            <h4 class="page-title">Add Client</h4>
         </div>
      </div>
   </div> -->
   <!-- end page title -->
   <div class="row mt-3">
      <div class="col-2">
         <ul class="nav_list">
            <li class="activeli">
               <a href="{{route('clients.index')}}"><span>List Drivers</span></a>
            </li>
            <li>
               <a href="{{route('arcchiveClients')}}"><span>Archived Drivers</span></a>
            </li>
            <li>
               <a href="{{route('expireClientDocuments')}}"><span>Expired Documents</span></a>
            </li>
            {{-- <li class="activeli">
               <a href="{{route('clients.create')}}"><span>New</span></a>
            </li> --}}
            <li>
               <a href="{{url('users/vehicles/show')}}"><span>All Vehicles</span></a>
            </li>
            {{-- <li>
                <a href="{{url('users/vehicles/add')}}"><span>Add Vehicles</span></a>
            </li> --}}
         </ul>
      </div>
      <div class="col-10 card">
         @if ($errors->any())
         <div class="alert alert-danger">
            <ul>
               @foreach ($errors->all() as $error)
               <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
         @endif

         <form method="POST" action="{{ route('clients.store') }}" enctype="multipart/form-data" name="gift_store">
            {{ csrf_field() }}
            <div class="card-body">
               <div class="form-group">
                  <div class="row">
                     <div class="col-sm-6">
                        <table class="table table-design-default">
                           <thead>
                              <tr class="formheading">
                                 <th>General Information</th>
                              </tr>
                           </thead>
                           <tr>
                              <td>
                                 <div class="row">
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Use salutation <input type="checkbox" name="check"></label>
                                       <select class="form-control salu" name="salutation">

                                          <option value="Mr">Mr</option>
                                          <option value="Mrs">Mrs</option>
                                          <option value="Miss">Miss</option>
                                          <option value="Ms">Ms</option>
                                          <option value="Mx">Mx</option>
                                          <option value="Dr">Doctor</option>

                                       </select>
                                    </div>
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">First Name <code>*</code></label>
                                       <input type="text" name="first_name" class="form-control" required="" placeholder="First Name">
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Middle Name <code>*</code></label>
                                       <input type="text" name="middle_name" class="form-control" placeholder="Middle Name" required="">
                                    </div>

                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Last Name <code>*</code></label>
                                       <input type="text" name="last_name" class="form-control" placeholder="Last Name" required="">
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Display Name: <code>*</code></label>
                                       <input type="text" name="display_name" class="form-control" placeholder="Display Name:" required="">
                                    </div>

                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Gender <code>*</code></label>
                                       <select class="form-control" name="gender" required="">
                                          <option disabled="">Select</option>
                                          <option value="Male">Male</option>
                                          <option value="Female">Female</option>
                                          <option value="Intersex">Intersex</option>
                                          <option value="Non-binary">Non-binary</option>
                                          <option value="Unspecified">Unspecified</option>
                                          <option value="Perfer not to say">Perfer not to say</option>
                                       </select>
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Date Of Birth <code>*</code></label>
                                       <input type="text" name="dob" class="form-control" id="datepicker" required="" placeholder="Date Of Birth">
                                    </div>
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Marital Status <code>*</code></label>
                                       <select class="form-control" name="marital_status" required="">
                                          <option disabled="">Select</option>
                                          <option value="Single">Single</option>
                                          <option value="Married">Married</option>
                                          <option value="De Facto">De Facto</option>
                                          <option value="Divorced">Divorced</option>
                                          <option value="Separated">Separated</option>
                                          <option value="Widowed">Widowed</option>
                                       </select>
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-sm-12 mt-2">
                                       <label for="exampleInputName1">Image <code>*</code></label>
                                       <input type="file" name="profileImage" class="form-control" required="">
                                    </div>
                                 </div>
                              </td>
                           </tr>
                           </tbody>
                        </table>
                     </div>
                     <div class="col-sm-6">
                        <table class="table table-design-default">
                           <thead>
                              <tr class="formheading">
                                 <th>Contact Information</th>
                              </tr>
                           </thead>
                           <tr>
                              <td>
                                 <div class="row">
                                    <div class="col-sm-12 mt-2">
                                       <label for="exampleInputName1">Address <code>*</code></label>
                                       <input type="text" name="address" class="form-control" required="" placeholder="Address" id="autocomplete">
                                       <input type="hidden" id="latitude" name="latitude" value="">
                                       <input type="hidden" id="longitude" name="longitude" value="">

                                    </div>
                                 </div>

                                 <div class="row">
                                    <div class="col-sm-12 mt-2">
                                       <label for="exampleInputName1">Unit/Apartment Number <code>*</code></label>
                                       <input type="text" name="appartment_number" class="form-control" placeholder="Unit/Apartment Number" required="">
                                    </div>
                                 </div>

                                 <div class="row">
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Mobile <code>*</code></label>
                                       <input type="tel" name="mobile" class="form-control pn" required="" placeholder="Mobile">
                                    </div>

                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Phone <code>*</code></label>
                                       <input type="text" name="phone" class="form-control pn" required="" placeholder="Phone">
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Email <code>*</code></label>
                                       <input type="email" name="email" class="form-control" required="" placeholder="Email">
                                    </div>
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Religion <code>*</code></label>
                                       <input type="text" name="religion" class="form-control" required="" placeholder="Religion">
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Nationality <code>*</code></label>
                                       <input type="text" name="nationality" class="form-control" required="" placeholder="Nationality">
                                    </div>
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Language Spoken <code>*</code></label>
                                       <select class="form-control" name="language_spoken[]" required="">
                                          <option disabled="">Select</option>\
                                          @foreach($language as $lang)
                                          <option value="{{$lang->code}}">{{$lang->language_name}}</option>
                                          @endforeach
                                       </select>
                                    </div>
                                 </div>
                              </td>
                           </tr>
                        </table>
                     </div>




                  </div>
                  <div class="row">
                     <div class="col-12">
                        <table class="table table-design-default">
                           <thead>
                              <tr class="formheading">
                                 <th>Vehicle Information</th>
                              </tr>
                           </thead>
                           <tr>
                              <td>
                                 <div class="row">
                                    <div class="col-sm-4 mt-2">
                                       <label for="exampleInputName1">Model <code>*</code></label>
                                       <input type="text" name="name" class="form-control" required="" placeholder="Model">


                                    </div>
                                    <div class="col-sm-4 mt-2">
                                       <label for="exampleInputName1">Seats<code>*</code></label>
                                       <input type="number" name="seats" class="form-control" required="" placeholder="Seats">

                                    </div>
                                    <div class="col-sm-4 mt-2">
                                       <label for="exampleInputName1">Chasis No<code>*</code></label>
                                       <input type="text" name="chasis_no" class="form-control" required="" placeholder="Chasis No">

                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-sm-4 mt-2">
                                       <label for="exampleInputName1">Color <code>*</code></label>
                                       <input type="text" name="color" class="form-control" required="" placeholder="Color">


                                    </div>
                                    <div class="col-sm-4 mt-2">
                                       <label for="exampleInputName1">Vehicle No<code>*</code></label>
                                       <input type="text" name="vehicle_no" class="form-control" required="" placeholder="Vehicle No">

                                    </div>
                                    <div class="col-sm-4 mt-2">
                                       <label for="exampleInputName1">Registration No<code>*</code></label>
                                       <input type="text" name="registration_no" class="form-control" required="" placeholder="Registration No">

                                    </div>
                                 </div>




                                 <div class="row">
                                  
                                    <div class="col-sm-4 mt-2">
                                       <label for="exampleInputName1">Fare<code>*</code></label>
                                      <input type="text" name="fare" class="form-control" required="">
                                    </div>  
                                    <div class="col-sm-4 mt-2">
                                       <label for="exampleInputName1">Description<code>*</code></label>
                                       <input type="text" name="description" class="form-control" required="">
                                    </div>
                                    <div class="col-sm-4 mt-2">
                                       <label for="exampleInputName1">Vehicle image <code>*</code></label>
                                       <input type="file" name="vehicleImage" class="form-control" required="">
                                    </div>


                              </td>
                           </tr>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
            <div class="card-footer">
               <button type="submit" class="btn btn-success" value="1" name="exit">Save</button>

               <a href="javascript:;" class="btn btn-danger" onclick="history.back()">Cancel</a>
            </div>
         </form>
      </div>
   </div>
   <!-- end col -->
</div>
<!-- end row -->
</div> <!-- container -->
@endsection

@section('script')
<script>
   function initAutocomplete() {
      var input = document.getElementById('autocomplete');
      var options = {
         //types: ['geocode'], // This restricts results to addresses
      };

      var autocomplete = new google.maps.places.Autocomplete(input, options);

      // Add a listener to capture the selected place
      autocomplete.addListener('place_changed', function() {
         var place = autocomplete.getPlace();

         if (!place.geometry) {
            console.log('Place details not available');
            return;
         }

         var latitude = place.geometry.location.lat();
         var longitude = place.geometry.location.lng();

         document.getElementById('latitude').value = latitude;
         document.getElementById('longitude').value = longitude;

      });
   }
</script>

<script>
   const GOOGLE_API_KEY = '{{env("GOOGLE_API_KEY")}}';


   function loadScript() {
      var script = document.createElement('script');
      script.type = 'text/javascript';
      script.src = `https://maps.googleapis.com/maps/api/js?key=` + GOOGLE_API_KEY + `&libraries=places&callback=initAutocomplete`;

      document.body.appendChild(script);
   }

   // Listen for the DOM content to be fully loaded, then load the script
   window.addEventListener('load', loadScript);
</script>


<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">



<script type="text/javascript">
   $(document).ready(function() {
      $("#datepicker").datepicker({
         maxDate: 0,
         dateFormat: 'dd-mm-yy'
      });
      $('.phone').keyup(function(e) {
         if (/\D/g.test(this.value)) {
            // Filter non-digits from input value.
            this.value = this.value.replace(/\D/g, '');
         }
      });

      $("input[type='checkbox']").click(function() {
         if ($(this).is(':checked')) {

            $('.salu').prop('disabled', false);
         } else {

            $('.salu').prop('disabled', true);
         }
      });


      // on change type ans show roles
      $('.type').change(function() {
         var curretValue = $(this).val();
         if (curretValue == 'Office User') {
            $('.role').show();
         } else {
            $('.role').hide();
         }
      });

      // when clickbox
      // $("input[type='checkbox']").click(function() { 
      // $('.type').change(function(){

      // });​

   });
</script>

@endsection