@extends('backend.layouts.app')
@section('title', ' | Profile')
@section('content')

    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Profile</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Profile
                        </li>
                    </ol>
                </div>
            </div> <!--end::Row-->
        </div> <!--end::Container-->
    </div> <!--end::App Content Header-->
    <!--begin::App Content-->
    <div class="app-content"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0 text-center">Update Profile</h4>
                        </div>
                        <form name="updateProfile" class="px-3" action="{{ route('admin.profile') }}" method="POST"
                            enctype="multipart/form-data" autocomplete="off">
                            @csrf
                            <div class="card-body">
                                <div class="text-center">
                                    <img id="profile_pic_output" alt="{{ $admin->name }}"
                                        src="{{ asset(!empty($admin->profile_pic) ? $admin->profile_pic : '/backend/assets/img/avatars/avatar.jpg') }}"
                                        class="rounded-circle img-responsive mt-2" width="128" height="128">
                                    <div class="mt-2">
                                        <input type="file" class="d-none" name="profile_pic" id="profile_pic" />
                                        <button type="button" id="uploadFile" class="btn btn-primary"><i
                                                class="fas fa-upload"></i>
                                            Upload</button>
                                    </div>
                                </div>
                                <div class="row mt-5">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="inputFirstName">First name</label>
                                        <input type="text" class="form-control" id="inputFirstName" name="first_name"
                                            value="{{ $admin->first_name }}" placeholder="First name">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="inputLastName">Last name</label>
                                        <input type="text" class="form-control" id="inputLastName" name="last_name"
                                            value="{{ $admin->last_name }}" placeholder="Last name">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="{{ $admin->email }}" placeholder="Email" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control phone-mask" id="phone" name="phone"
                                        value="{{ \App\Http\Controllers\CommonFunction::formatPhone($admin->phone) }}"
                                        placeholder="Phone">
                                </div>
                                <label class="form-label">Reset Password</label>
                                <button type="button" class="btn btn-xs btn-toggle reset-form" data-bs-toggle="button"
                                    aria-pressed="true" autocomplete="off">
                                    <div class="handle">
                                        <input type="checkbox" name="reset_password" value="1" style="display:none" />
                                    </div>
                                </button>
                            </div>

                            <div class="card-body reset-pass-section" style="display: none">
                                <div class="mb-3">
                                    <label class="form-label" for="current_password">Current password:</label>
                                    <input type="password" class="form-control" name="current_password"
                                        id="current_password">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="password">New password: <a class="bs-tooltip"
                                            href="javascrip:void(0)" data-bs-placement="right"
                                            title="A minimum of 8 characters, and should contain at least 1 uppercase, 1 number, 1 special character."><i
                                                class="fa-solid fa-circle-exclamation"></i></a></label>
                                    <input type="password" name="password" class="form-control" id="password">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="password_confirmation">Verify password:</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" data-rule-equalto="#password">
                                </div>
                            </div>

                            <div class="card-footer text-center">
                                <button type="submit" class="btn btn-primary">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-scripts')
    <script type="text/javascript">
        window.addEventListener('load', function() {
            const rules = {
                phone: {
                    phoneNo: true
                }
            };
            let validator = validateForm($('form[name="updateProfile"]'), rules, {})
            $('#uploadFile').on('click', function(e) {
                e.preventDefault();
                $(this).parent().children('input[name="profile_pic"]').click()
            })
            $('input[name="profile_pic"]').on('change', function(e) {
                const output = document.getElementById('profile_pic_output');
                output.src = URL.createObjectURL(e.target.files[0]);
                output.onload = function() {
                    URL.revokeObjectURL(output.src) // free memory
                }
            })
            $('.reset-form').on('click', function() {
                if ($(this).hasClass('active')) {
                    $('.reset-pass-section').fadeIn(function() {
                        $(this).find('input[type="password"]').prop('required', true)
                    })
                    $('input[name="reset_password"]').prop('checked', true)
                } else {
                    $('.reset-pass-section').fadeOut(function() {
                        $(this).find('input[type="password"]').prop('required', false)
                    })
                    $('input[name="reset_password"]').prop('checked', false)
                }
            })

            $('form[name="updateProfile"]').on('submit', function(e) {
                sanitizePhoneInputs(this);
                if (!validator.form()) {
                    e.preventDefault();
                }
            });
        })
    </script>
@endpush
