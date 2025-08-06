<!-- bundle -->
<!-- Vendor js -->
<script src="{{asset('assets/js/vendor.min.js')}}"></script>
@yield('script')
<!-- App js -->
<script src="{{asset('assets/js/app.min.js')}}"></script>
<!-- @if (@$flag != '2') 
<script src="{{asset('assets/js/appd.min.js')}}"></script>
@endif
@if (@$flag == '2') 
<script src="{{asset('assets/js/app.min.js')}}"></script>
@endif -->
@yield('script-bottom')
