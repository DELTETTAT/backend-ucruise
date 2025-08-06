@extends('layouts.vertical', ['title' => 'Add Shift Type'])
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
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Add Shift Type</a></li>
                  
               </ol>
            </div>
            <h4 class="page-title">Add Shift Type</h4>
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
         
         <form method="POST" action="{{ route('shift-type.store') }}" enctype="multipart/form-data" name="gift_store">
            {{ csrf_field() }}
            <div class="card-body">
               <div class="form-group">
                  <div class="row">

                     

                      <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Name <code>*</code></label>
                         <input type="text" name="name" class="form-control"   required="" placeholder="Name">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">External id <code>*</code></label>
                         <input type="text" name="external_id" class="form-control"   required="" placeholder="Name">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Color <code>*</code></label>
                         <input type="color" name="color" class="form-control" >
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
 


<script type="text/javascript">
  $(document).ready(function(){
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