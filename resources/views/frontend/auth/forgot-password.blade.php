@extends('frontend.layouts.app')
@section('title', ' | Forgot Password')
@section('content')
    <main>
        <!-- bannner start -->
        <section>
            <div class="bannerBack innerBanner" style="background-image: url({{ asset('assets/img/banner.png') }});">
                <div class="container banContainer">
                    <div class="df-column bannerCont" data-aos="fade-up">
                        <h2 class="bannerHead">Forgot Password</h2>
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
                                Forgot Password
                            </h3>
                            <form data-aos="fade-up" class="log-frm" name="reset_form" id="reset_form"
                                action="{{ route('password.email') }}" method="POST">
                                @csrf
                                <div class="df-column w-100">
                                    <input type="email" placeholder="Email Address*" id="userEmail" name="user_email"
                                        required>
                                </div>
                                <input type="button" class="primary-btn reset-link-btn" value="Submit">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@push('custom-scripts')
    <script>
        window.addEventListener('load', function() {
            $('.reset-link-btn').on('click', function(e) {
                e.preventDefault();
                let validator = validateForm($('form[name="reset_form"]'), {}, {})
                let url = $('#reset_form').attr('action')
                let method = $('#reset_form').attr('method')
                let formData = $('form[name="reset_form"]').serialize();
                if (validator.form()) {
                    $.ajax({
                        url: url,
                        method: method,
                        data: formData,
                        beforeSend: function() {
                            $('#ajax-loader').fadeIn();
                        },
                        success: function(response) {
                            $('#ajax-loader').fadeOut();
                            swalAlert2(response.status, response.message, response.redirect)
                            setTimeout(function() {
                                window.location.href = response.redirect
                            }, 1500);
                        },
                        error: function(response) {
                            $('#ajax-loader').fadeOut();
                            swalAlert(response.responseJSON.status, response.responseJSON
                                .errors, 4000)
                        }
                    });
                }
            })
        })
    </script>
@endpush
