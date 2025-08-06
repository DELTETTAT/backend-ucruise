<!DOCTYPE html>
<html lang="en">
    <head>
        @include('layouts.shared.title-meta', ['title' => "Forgot Password"])

        @include('layouts.shared.head-css')
    </head>
    <style>
    img {
    vertical-align: middle;
    border-style: none;
    background: #665bdd;
    padding: 10px;
    border-radius: 8px;
}
</style>
    <body class="authentication-bg authentication-bg-pattern">

        <div class="account-pages mt-5 mb-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card bg-pattern">

                            <div class="card-body p-4">
                                
                                <div class="text-center w-75 m-auto">
                                    <div class="auth-logo">
                                        <a href=" " class="logo logo-dark text-center">
                                            <span class="logo-lg">
                                                <img src="{{env('LOGO')}}" alt="" height="50">
                                            </span>
                                        </a>
                    
                                        <a href=" " class="logo logo-light text-center">
                                            <span class="logo-lg">
                                                <img src="{{env('LOGO')}}" alt="" height="50">
                                            </span>
                                        </a>
                                    </div>
                                    <p class="text-muted mb-4 mt-3">Enter your OTP and Password to reset your password.</p>
                                </div>
                                @if ($message = Session::get('error'))  
                                <div class="alert alert-danger alert-block">  
                                <button type="button" class="close" data-dismiss="alert">X</button>   
                                <strong>{{ $message }}</strong>  
                                </div>  
                                @endif

                                @if ($message = Session::get('success'))  
                                <div class="alert alert-success alert-block">  
                                <button type="button" class="close" data-dismiss="alert">X</button>   
                                <strong>{{ $message }}</strong>  
                                </div>  
                                @endif
                                <form action="{{url('admin/savePassword')}}" method="POST" > 
                                    @csrf
                                    <input type="hidden" value="{{$email}}" name="email">
                                    <div class="form-group mb-3">
                                        <label for="emailaddress">Password</label>
                                        <input class="form-control" type="text"   name="password" required="" placeholder="Enter your Password">
                                        @if ($errors->has('password'))
                                            <span class="text-danger">{{ $errors->first('confirm_password') }}</span>
                                        @endif
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="emailaddress">Confirm Password</label>
                                        <input class="form-control" type="password" id="emailaddress" name="confirm_password" required="" placeholder="Enter your Confirm Password">
                                    </div>
                                    
                                     

                                    <div class="form-group mb-0 text-center">
                                        <button class="btn btn-primary btn-block" type="submit"> Reset Password </button>
                                    </div>

                                </form>

                            </div> <!-- end card-body -->
                        </div>
                        <!-- end card -->

                        <div class="row mt-3">
                            <div class="col-12 text-center">
                                <p class="text-white-50">Back to <a href="{{url('admin/login')}}" class="text-white ml-1"><b>Log in</b></a></p>
                            </div> <!-- end col -->
                        </div>
                        <!-- end row -->

                    </div> <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end page -->


        <footer class="footer footer-alt">
            <script>document.write(new Date().getFullYear())</script> &copy; {{env('APP_NAME')}}
        </footer>
        
        @include('layouts.shared.footer-script')
        
    </body>
</html>