@extends('layouts.vertical', ['title' => 'Edit Client'])
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
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Clients</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Edit Client</a></li>
               </ol>
            </div>
            <h4 class="page-title">Edit Client</h4>
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

         <form method="POST" action="{{ route('clients.update',[$edit->id])}}" enctype="multipart/form-data" name="gift_store">
            <input type="hidden" name="_method" value="PUT">
            {{ csrf_field() }}
            <div class="card-body">
               <div class="form-group">
                  <div class="row">

                     <div class="col-sm-2 mt-2">
                        <label for="exampleInputName1">Use salutation <input type="checkbox" name="check" <?php if ($edit->salutation) {
                                                                                                               echo 'checked';
                                                                                                            } ?>></label>
                        <select class="form-control salu" name="salutation" <?php if (!$edit->salutation) {
                                                                                 echo 'disabled';
                                                                              } ?>>

                           <option value="Mr" <?php if ($edit->salutation == 'Mr') {
                                                   echo 'selected';
                                                } ?>>Mr</option>
                           <option value="Mrs" <?php if ($edit->salutation == 'Mrs') {
                                                   echo 'selected';
                                                } ?>>Mrs</option>
                           <option value="Miss" <?php if ($edit->salutation == 'Miss') {
                                                   echo 'selected';
                                                } ?>>Miss</option>
                           <option value="Ms" <?php if ($edit->salutation == 'Ms') {
                                                   echo 'selected';
                                                } ?>>Ms</option>
                           <option value="Mx" <?php if ($edit->salutation == 'Mx') {
                                                   echo 'selected';
                                                } ?>>Mx</option>
                           <option value="Dr" <?php if ($edit->salutation == 'Dr') {
                                                   echo 'selected';
                                                } ?>>Doctor</option>

                        </select>
                     </div>

                     <div class="col-sm-4 mt-2">
                        <label for="exampleInputName1">First Name <code>*</code></label>
                        <input type="text" name="first_name" class="form-control" value="{{$edit->first_name}}" required="" placeholder="First Name">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Middle Name <code>*</code></label>
                        <input type="text" name="middle_name" class="form-control" value="{{$edit->middle_name}}" placeholder="Middle Name">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Last Name <code>*</code></label>
                        <input type="text" name="last_name" class="form-control" value="{{$edit->last_name}}" placeholder="Last Name">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Display Name: <code>*</code></label>
                        <input type="text" name="display_name" class="form-control" value="{{$edit->display_name}}" placeholder="Display Name:">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Gender <code>*</code></label>
                        <select class="form-control" name="gender" required="">
                           <option disabled="">Select</option>
                           <option value="Male" <?php if ($edit->gender == 'Male') {
                                                   echo 'selected';
                                                } ?>>Male</option>
                           <option value="Female" <?php if ($edit->gender == 'Female') {
                                                      echo 'selected';
                                                   } ?>>Female</option>
                           <option value="Intersex" <?php if ($edit->gender == 'Intersex') {
                                                         echo 'selected';
                                                      } ?>>Intersex</option>
                           <option value="Non-binary" <?php if ($edit->gender == 'Non-binary') {
                                                         echo 'selected';
                                                      } ?>>Non-binary</option>
                           <option value="Unspecified" <?php if ($edit->gender == 'Unspecified') {
                                                            echo 'selected';
                                                         } ?>>Unspecified</option>
                           <option value="Perfer not to say" <?php if ($edit->gender == 'Perfer not to say') {
                                                                  echo 'selected';
                                                               } ?>>Perfer not to say</option>
                        </select>
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Date Of Birth <code>*</code></label>
                        <input type="text" name="dob" class="form-control" value="{{$edit->dob}}" id="datepicker" required="" placeholder="Date Of Birth">
                     </div>


                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Address </label>
                        <input type="text" name="address" class="form-control" value="{{$edit->address}}" required="" placeholder="Address" id="autocomplete">
                        <input type="hidden" id="latitude" name="latitude" value="{{$edit->latitude}}">
                        <input type="hidden" id="longitude" name="longitude" value="{{$edit->longitude}}">
                     </div>


                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Unit/Apartment Number <code>*</code></label>
                        <input type="text" name="appartment_number" class="form-control" value="{{$edit->appartment_number}}" placeholder="Unit/Apartment Number">
                     </div>


                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Mobile</label>
                        <input type="text" name="mobile" class="form-control phone pn" value="{{$edit->mobile}}" required="" placeholder="Mobile">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Phone <code>*</code></label>
                        <input type="text" name="phone" class="form-control phone pn" value="{{$edit->phone}}" required="" placeholder="Phone">
                     </div>


                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Email <code>*</code></label>
                        <input type="email" name="email" class="form-control" value="{{$edit->email}}" required="" placeholder="Email" readonly>
                     </div>


                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Religion <code>*</code></label>
                        <input type="text" name="religion" class="form-control" value="{{$edit->religion}}" required="" placeholder="Religion">
                     </div>


                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Marital Status <code>*</code></label>
                        <select class="form-control" name="marital_status" required="">
                           <option disabled="">Select</option>
                           <option value="Single" <?php if ($edit->marital_status == 'Single') {
                                                      echo 'selected';
                                                   } ?>>Single</option>
                           <option value="Married" <?php if ($edit->marital_status == 'Married') {
                                                      echo 'selected';
                                                   } ?>>Married</option>
                           <option value="De Facto" <?php if ($edit->marital_status == 'De Facto') {
                                                         echo 'selected';
                                                      } ?>>De Facto</option>
                           <option value="Divorced" <?php if ($edit->marital_status == 'Divorced') {
                                                         echo 'selected';
                                                      } ?>>Divorced</option>
                           <option value="Separated" <?php if ($edit->marital_status == 'Separated') {
                                                         echo 'selected';
                                                      } ?>>Separated</option>
                           <option value="Widowed" <?php if ($edit->marital_status == 'Widowed') {
                                                      echo 'selected';
                                                   } ?>>Widowed</option>
                        </select>
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Nationality <code>*</code></label>
                        <input type="text" name="nationality" class="form-control" value="{{$edit->nationality}}" required="" placeholder="Nationality">
                     </div>


                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Language Spoken <code>*</code></label>
                        <?php
                        $ids = explode(',', $edit->language_spoken);
                        // dd( $ids);
                        ?>
                        <select class="form-control" name="language_spoken[]" required="">
                           <option disabled="">Select</option>\
                           @foreach($language as $lang)
                           <option value="{{$lang->code}}" <?php if (in_array($lang->code, $ids)) {
                                                               echo 'selected';
                                                            } ?>>{{$lang->language_name}}</option>
                           @endforeach
                        </select>
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Image <code>*</code></label>
                        <input type="file" name="profileImage" class="form-control">
 
                        <img class="Aimage" src="{{url('/images')}}/{{@$edit->profile_image}}" onerror="this.src='https://t3.ftcdn.net/jpg/03/45/05/92/240_F_345059232_CPieT8RIWOUk4JqBkkWkIETYAkmz2b75.jpg'" style="width: 50px;height:50px">
                     </div>

                  
                        <div class="col-12">

                           <thead>
                              <tr>
                                 <th>Vehicle Information</th>
                              </tr>
                           </thead>
                           <tr>
                              <td>
                                 <div class="row">
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Model <code>*</code></label>
                                       <input type="text" name="name" class="form-control"  value="{{$edit->vehicle->name}}" placeholder="Model">


                                    </div>
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Seats<code>*</code></label>
                                       <input type="number" name="seats" class="form-control" value="{{$edit->vehicle->seats}}" required="" placeholder="Seats">

                                    </div>

                                 </div>
                                 <div class="row">
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Chasis No<code>*</code></label>
                                       <input type="text" name="chasis_no" class="form-control" value="{{$edit->vehicle->chasis_no}}" required="" placeholder="Chasis No">

                                    </div>
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Color <code>*</code></label>
                                       <input type="text" name="color" class="form-control" required="" value="{{$edit->vehicle->color}}" placeholder="Color">


                                    </div>

                                 </div>

                                 <div class="row">
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Vehicle No<code>*</code></label>
                                       <input type="text" name="vehicle_no" class="form-control" required="" value="{{$edit->vehicle->vehicle_no}}" placeholder="Vehicle No">

                                    </div>
                                    <div class="col-sm-6 mt-2">
                                       <label for="exampleInputName1">Registration No<code>*</code></label>
                                       <input type="text" name="registration_no" class="form-control" required="" value="{{$edit->vehicle->registration_no}}" placeholder="Registration No">

                                    </div>




                              </td>
                              <td>
                                 <div class="col-sm-6 mt-2">
                                    <label for="exampleInputName1">Fare<code>*</code></label>
                                    <input type="text" name="fare" class="form-control" required="" value="{{$edit->vehicle->fare}}" placeholder="Fare">
                                 </div>
                                 <div class="col-sm-6 mt-2">
                                    <label for="exampleInputName1">Description<code>*</code></label>
                                    <input type="text" name="description" class="form-control" required="" value="{{$edit->vehicle->description}}" placeholder="Description">
                                 </div>
                              </td>
                           </tr>
                           <tr>
                              <div class="col-sm-6 mt-2">
                                 <label for="exampleInputName1">Vehicle image <code>*</code></label>
                                 <input type="file" name="vehicleImage" class="form-control" required="">
                                 <img class="Aimage" src="{{url('/images/vehicles')}}/{{@$edit->vehicle->image}}" onerror="this.src='https://t3.ftcdn.net/jpg/03/45/05/92/240_F_345059232_CPieT8RIWOUk4JqBkkWkIETYAkmz2b75.jpg'" style="width: 50px;height:50px">
                              </div>
                           </tr>

                        </div>
                     </div>


                  </div>
               </div>
            </div>
      </div>
      <div class="card-footer">
         <button type="submit" class="btn btn-success" value="1" name="exit">Save</button>

         <a href="javascript:;" class="btn btn-defult" onclick="history.back()">Cancel</a>
      </div>
      </form>
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