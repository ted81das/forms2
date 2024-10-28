@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">    
        <div class="login-box">
          <!-- /.login-logo -->
            <div class="card card-outline card-primary">
                <div class="card-header text-center">
                    <a href="{{action([\App\Http\Controllers\HomeController::class, 'index'])}}" class="h1">
                        <b>{{ config('app.name', 'Laravel') }}</b>
                    </a>
                </div>
                <div class="card-body">
                    <p class="login-box-msg">
                       {{ __('messages.login_to_start_your_session') }}
                    </p>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        @if(config('app.env') == 'demo')
                            @php
                                $email = 'admin@admin.com';
                                $pwd = '12345678';
                            @endphp
                        @else
                            @php
                                $email = '';
                                $pwd = '';
                            @endphp
                        @endif

                        <div class="input-group mb-3">
                            <input type="email" id="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{old('email') ?? $email}}" required autocomplete="email" autofocus placeholder="{{ __('E-Mail Address') }}">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="input-group mb-3">
                            <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" value="{{$pwd}}" placeholder="{{ __('Password') }}">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-8">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                            <!-- /.col -->
                            <div class="col-4">
                                <button type="submit" class="btn btn-primary btn-block">
                                    {{ __('Login') }}
                                </button>
                            </div>
                            <!-- /.col -->
                        </div>
                    </form>
                    <!-- /.social-auth-links -->
                    
                    <div class="social-auth-links text-center mt-2 mb-3">
                        <p>- {{ __('OR') }} -</p>
                        @if (Route::has('password.request'))
                            <a class="btn btn-link" href="{{ route('password.request') }}">
                                {{ __('messages.forgot_password') }} <b>@lang('messages.reset')</b>
                            </a>
                        @endif
                        @if (Route::has('register'))
                            <a class="btn btn-link" href="{{ route('register') }}">
                                {{ __('messages.dont_have_an_account') }} <b>@lang('messages.register')</b>
                            </a>
                        @endif
                    </div>
                    
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
@endsection
@section('footer')
<script type="text/javascript">
    $(document).ready(function(){
        @if (!empty(session('status')) && session('status')['success'] == false)
            var msg = '{!!session('status')['msg']!!}';
            toastr.error(msg);
        @endif
    });
</script>
@endsection

