@extends('frontend.layouts.app')
@section('title', ' | Register')
@section('content')
    <main>
        <!-- bannner start -->
        <section>
            <div class="bannerBack innerBanner" style="background-image: url({{ asset('assets/img/banner.png') }});">
                <div class="container banContainer">
                    <div class="df-column bannerCont" data-aos="fade-up">
                        <h2 class="bannerHead">Register</h2>
                    </div>
                </div>
            </div>
        </section>

        <!-- form start -->
        <section>
            <div class="container">
                <div class="panel">
                    <div class="text-center reg-content">
                        <div class="mb-4">
                            <h3 data-aos="fade-up" class="mb-3">
                                Register
                            </h3>
                            <p data-aos="fade-up">Thank you for choosing to register for a user account!<br>
                                Your registration will ensure you have the best possible user experience. Registration
                                allows you to access features such as creating a custom resource list, recommending new
                                resources and suggesting edits to existing resources. Additionally, registration allows
                                us
                                to better meet your needs through periodic surveys and promotions.<br>
                                Registration is free.
                            </p>
                        </div>
                        <div class="dark-frm" data-aos="fade-up">
                            <form action="{{ route('register-submit') }}" name="register_form" id="register_form"
                                data-aos="fade-up" class="reg-frm" method="post">
                                @csrf
                                <div class="df-column align-items-center">
                                    <input type="text" placeholder="Your Name*" id="registerName" name="register_name"
                                        required>
                                    <input type="tel" placeholder="Phone Number*" id="registerPhone"
                                        name="register_phone" class="phone-mask" required>
                                    <input type="email" placeholder="Email Address*" id="registerEmail"
                                        name="register_email" required>
                                    <input type="text" placeholder="Zip code*" id="registerZip" name="register_zip"
                                        required>
                                    <input type="button" class="primary-btn mt-3 register-btn" value="Register">
                                </div>
                            </form>
                        </div>
                        <p data-aos="fade-up" class="sz-16">Already Have An Account? <a href="{{ url('login') }}"
                                class="fn-smb">Login</a></p>
                        <p data-aos="fade-up"><span class="fn-smb">Privacy and Security:</span> Our Promise to You.
                            Maintaining your privacy and
                            security is our top
                            priority. Information furnished through the registration process and/or while an active user
                            of our site will never be shared with third-parties. Personally identifiable information
                            (PII) such as name, email address, phone and postal code are encrypted and maintained in
                            accordance with our industry leading privacy and data security policies.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
@push('custom-scripts')
    <script>
        let rules = {
            register_phone: {
                required: true,
                phoneNo: true
            },
            register_zip: {
                required: true,
                postalCode: true
            }
        }
        $('.register-btn').on('click', function(e) {
            e.preventDefault();
            let validator = validateForm($('form[name="register_form"]'), rules, {})
            let url = $('#register_form').attr('action')
            let method = $('#register_form').attr('method')
            const phoneSnapshots = sanitizePhoneInputs($('#register_form'));
            let formData = $('form[name="register_form"]').serialize();
            phoneSnapshots.forEach(function(snapshot) {
                snapshot.el.val(snapshot.formatted);
            });
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
                        swalAlert(response.responseJSON.status, response.responseJSON.errors, 4000)
                    }
                });
            }
        })
    </script>
@endpush
