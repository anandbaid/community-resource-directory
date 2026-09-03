@extends('frontend.layouts.app')
@section('title', ' | Login')
@section('content')
    <main>
        <!-- bannner start -->
        <section>
            <div class="bannerBack innerBanner" style="background-image: url({{ asset('assets/img/banner.png') }});">
                <div class="container banContainer">
                    <div class="df-column bannerCont" data-aos="fade-up">
                        <h2 class="bannerHead">Login</h2>
                    </div>
                </div>
            </div>
        </section>

        <!-- form start -->
        <section>
            <div class="container">
                <div class="panel">
                    <div class="cntctFrmSec text-center" data-aos="fade-up">
                        <div class="dark-frm">
                            <h3 data-aos="fade-up">
                                Login
                            </h3>
                            <form data-aos="fade-up" class="log-frm" name="loginForm" action="{{ route('login') }}"
                                method="POST">
                                @csrf
                                <div class="df-column w-100">
                                    <input type="hidden" name="redirect" value="{{ $redirect }}">
                                    <input type="email" placeholder="Email Address*" id="loginEmail" name="login_email"
                                        required>
                                    <div class="password-eye">
                                        <input type="password" placeholder="Password*" id="loginPass" name="login_password"
                                            required class="custom-password">
                                        <i class="fa-solid fa-eye password-eye-icon"></i>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center w-100">
                                    <label class="custom-check-label">
                                        {{-- <input type="checkbox" name="remember_me" id="" value="1"
                                            class="custom-checkbox">Remember Me</label> --}}
                                    <a href="{{ url('password-reset') }}" class="textLink">Forgot Passwoord</a>
                                </div>
                                <input type="button" class="primary-btn login-btn" value="Login">
                            </form>
                        </div>
                        <p data-aos="fade-up" class="sz-16">Don’t Have An Account? <a href="{{ url('register') }}"
                                class="fn-smb">Register
                                Now</a></p>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@push('custom-scripts')
    <script>
        window.addEventListener('load', function() {
            $('.login-btn').on('click', function(e) {
                e.preventDefault();
                let validator = validateForm($('form[name="loginForm"]'), {}, {})
                if (validator.form()) {
                    $('form[name="loginForm"]').submit();
                }
            })
        })
    </script>
@endpush
