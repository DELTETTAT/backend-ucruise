@extends('layouts.vertical', ['title' => 'Add Staff'])
@section('content')
<!-- Start Content-->
<div class="container-fluid">
   <!-- start page title -->
   <!-- <div class="row">
      <div class="col-12">
         <div class="page-title-box">
            <div class="page-title-right">
               <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Users</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Add Staff</a></li>
               </ol>
            </div>
            <h4 class="page-title">Add Staff</h4>
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
            <li>
               <a href="{{route('arcchiveStaff')}}"><span>Archived Staff</span></a>
            </li>
            <li>
               <a href="{{route('expireStaffDocuments')}}"><span>Expired Documents</span></a>
            </li>
            <li>
               <a href="{{route('teams')}}"><span>List Teams</span></a>
            </li>
            <li class="activeli">
               <a href="{{url('users/add-staff')}}"><span>New</span></a>
            </li>
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

         <form method="POST" action="{{ route('staff.store') }}" enctype="multipart/form-data" name="gift_store">
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
                                       <label for="exampleInputName1">Name <code>*</code></label>
                                       <input type="text" name="first_name" class="form-control" required="" placeholder="Name">
                                    </div>
                                 </div>
                                 <div class="row">
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
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Date Of Birth <code>*</code></label>
                                       <input type="text" name="dob" class="form-control" id="datepicker" required="" placeholder="Date Of Birth">
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Type <code>*</code></label>
                                       <select class="form-control type" name="type" required="">
                                          <option disabled="">Select</option>
                                          <option value="carer">Carer</option>
                                          <option value="Office User">Office User</option>

                                       </select>
                                    </div>
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Employement Type <code>*</code></label>
                                       <select class="form-control" name="employement_type" required="">
                                          <option disabled="">Select</option>
                                          <option value="Employee">Employee</option>
                                          <option value="Contractor">Contractor</option>
                                       </select>
                                    </div>
                                    <div class="col-sm-12 mt-2 role" style="display:none">
                                       <label for="exampleInputName1">Role <code>*</code></label>
                                       <select class="form-control" name="role" required="">
                                          <option disabled="">Select</option>
                                          @foreach($roles as $role)
                                          <option value="{{$role->name}}">{{ucfirst($role->name)}}</option>
                                          @endforeach
                                       </select>
                                    </div>
                                 </div>
                              </td>
                           </tr>
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
                                       <input type="text" id="autocomplete" name="address" class="form-control" required="" placeholder="Address">
                                       <input type="hidden" id="latitude" name="latitude" value="">
                                       <input type="hidden" id="longitude" name="longitude" value="">
                                       <input type="hidden" id="postalCode" name="postalCode" value="">
                                       <input type="hidden" id="selectedAddress" name="selectedAddress" value="">
                                    
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Mobile <code>*</code></label>
                                       <input type="text" name="mobile" class="form-control phone pn" required="" placeholder="Mobile">
                                    </div>

                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Phone <code>*</code></label>
                                       <input type="text" name="phone" class="form-control phone pn" required="" placeholder="Phone">
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-sm-12 mt-2">
                                       <label for="exampleInputName1">Email <code>*</code></label>
                                       <input type="text" name="email" class="form-control" required="" placeholder="Email">
                                    </div>
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

       // Get latitude and longitude
       var latitude = place.geometry.location.lat();
       var longitude = place.geometry.location.lng();

       // Extract postal code (if available)
       var postalCode = '';
       for (var i = 0; i < place.address_components.length; i++) {
         for (var j = 0; j < place.address_components[i].types.length; j++) {
           if (place.address_components[i].types[j] === 'postal_code') {
             postalCode = place.address_components[i].long_name;
             break;
           }
         }
       }
        
       // Store the selected place data
       document.getElementById('latitude').value = latitude;
       document.getElementById('longitude').value = longitude;
       document.getElementById('postalCode').value = postalCode;
       document.getElementById('selectedAddress').value = place.formatted_address;

       console.log('Selected Place:', place.formatted_address);
     });
   }
 </script>

 <script>
   // This is your API key from the Google Cloud Console
   const GOOGLE_API_KEY = '{{env("GOOGLE_API_KEY")}}';

   // Load the Google Places API and call the initAutocomplete() function
   function loadScript() {
     var script = document.createElement('script');
     script.type = 'text/javascript';
     script.src = `https://maps.googleapis.com/maps/api/js?key=`+GOOGLE_API_KEY+`&libraries=places&callback=initAutocomplete`;

     document.body.appendChild(script);
   }

   // Listen for the DOM content to be fully loaded, then load the script
   window.addEventListener('load', loadScript);
 </script>
 
@endsection