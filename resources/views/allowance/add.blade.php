@extends('layouts.vertical', ['title' => 'Add Allowance'])
@section('content')
<style>
   .select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #e9e9e9;
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
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Allowances</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Add Allowance</a></li>
               </ol>
            </div>
            <h4 class="page-title">Add Allowance</h4>
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
         
         <form method="POST" action="{{ route('allowance.store') }}" enctype="multipart/form-data" name="gift_store">
            {{ csrf_field() }}
            <div class="card-body">
               <div class="form-group">
                  <div class="row">

                  

                     
                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Name <code>*</code></label>
                         <input type="text" name="name" class="form-control"   required="" placeholder="Name">
                     </div>
 
                     <div class="col-sm-6 mt-2 role" >
                        <label for="exampleInputName1">Type <code>*</code></label>
                        <select class="form-control js-example-basic-single"  name="type" required="">
                             <option disabled="">Select</option>
                             @foreach($type as $value)
                             <option value="{{$value->id}}">{{$value->name}}</option>
                             @endforeach
                               
                         </select> 
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Value <code>*</code></label>
                         <input type="number" name="value" class="form-control"   required="" placeholder="Value">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Xero pay Item <code>*</code></label>
                         <input type="text" name="xero_pay_item" class="form-control"   required="" placeholder="Xero pay Item">
                     </div>

                      
                  </div>
               </div>
            </div>
      </div>
      <div class="card-footer">
      <button type="submit" class="btn btn-primary" value="1" name="exit">Save and Exit</button>
       
      <a href="javascript:;" class="btn btn-danger" onclick="history.back()" >Back</a>
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

<script type="text/javascript">
  $(document).ready(function(){

   $('.js-example-basic-single').select2();

   $( "#datepicker" ).datepicker({  maxDate: 0,dateFormat: 'dd-mm-yy' });
         $('.phone').keyup(function(e){
            if (/\D/g.test(this.value))
            {
               // Filter non-digits from input value.
               this.value = this.value.replace(/\D/g, '');
            }
      });

      $("input[type='checkbox']").click(function(){
         if ($(this).is(':checked')) {
         
            $('.salu').prop('disabled', false);
         } else {
              
            $('.salu').prop('disabled', true);
         }
      });


      // on change type ans show roles
      $('.type').change(function(){
         var curretValue =  $(this).val();
         if(curretValue == 'Office User'){
            $('.role').show();
         }else{
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