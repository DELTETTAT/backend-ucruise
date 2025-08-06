@extends('layouts.vertical', ['title' => 'Add Subscription'])
@section('content')
<!-- Start Content-->
<div class="container-fluid">
   <!-- start page title -->
   <div class="row">
      <div class="col-12">
         <div class="page-title-box">
            <div class="page-title-right">
               <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Subscription</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Add Subscription</a></li>
               </ol>
            </div>
            <h4 class="page-title">Add Subscription</h4>
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

         <form method="POST" action="{{ route('subscription.store') }}" enctype="multipart/form-data" name="gift_store">
            {{ csrf_field() }}
            <div class="card-body">
               <div class="form-group">
                  <div class="row">

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Title <code>*</code></label>
                        <input type="text" name="title" class="form-control" required="" placeholder="Title">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Price <code>*</code></label>
                        <input type="number" name="price" class="form-control" required="" placeholder="Price">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Description <code>*</code></label>
                        <textarea name="description" class="form-control" required=""></textarea>
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Features <code>*</code></label>
                        <select class="form-control js-example-basic-single featureSelect" multiple="multiple" name="feature[]">
                        
                        <option disabled="">Select</option>
                        @foreach($features as $feature)
                        <option value="{{$feature->id}}">{{$feature->name}}</option>
                       
                        @endforeach
                     </select>
                     </div>
                    
                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Billing Cycle <code>*</code></label>
                         <select class="form-control" name="billing_cycle" required="">
                             <option value="monthly">Monthly</option>
                             <option value="yearly">Yearly</option>
                         </select>
                     </div>



                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Status <code>*</code></label>
                        <select class="form-control" name="status" required="">
                           <option disabled="">Select</option>
                           <option value="1">Active</option>
                           <option value="0">In-Active</option>
                        </select>
                     </div>
                  </div>
               </div>
            </div>
      </div>
      <div class="card-footer">
         <button type="submit" class="btn btn-primary" value="1" name="exit">Save and Exit</button>

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

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script type="text/javascript">
   $(document).ready(function() {
      $('.gift').change(function() {
         let selectedVal = $(this).val();
         if (selectedVal == 'Animated Gift') {
            $('.gif').show();
         } else {
            $('.gif').hide();
         }
      });
   });
   
   $('.js-example-basic-single').select2();
   $('.js-example-basic-single').select2({
            tags: true,
            tokenSeparators: [',', ' '],
            maximumSelectionLength: 10 // Set the maximum number of selections
        });
    
</script>
@endsection