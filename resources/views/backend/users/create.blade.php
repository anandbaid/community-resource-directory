@extends('backend.layouts.app')

@section('title', ' | Create User')
@section('content')
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Create User</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Create User
                        </li>
                    </ol>
                </div>
            </div> <!--end::Row-->
        </div> <!--end::Container-->
    </div> <!--end::App Content Header-->


    <div class="app-content"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row justify-content-center">
                <div class="col-md-7">
                    <div class="card">
                        <h4 class="card-title mt-2 mb-0 text-center">User Details</h4>

                        <form name="createUser" class="px-3" action="{{ route('admin.user.store') }}" method="POST"
                            enctype="multipart/form-data" autocomplete="off">
                            @csrf
                            <div class="card-body">
                                <div class="text-center">
                                    <img id="profile_pic_output" alt=""
                                        src="{{ asset('assets/img/user-placeholder.png') }}"
                                        class="rounded-circle img-responsive mt-2" width="100" height="100">
                                    <div class="mt-2">
                                        <input type="file" class="d-none" name="profile_pic" id="profile_pic" />
                                        <button type="button" id="uploadFile" class="btn btn-sm btn-primary"><i
                                                class="fas fa-upload"></i>
                                            Upload</button>
                                    </div>
                                </div>
                                <div class="row mt-5">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="inputFirstName">First name</label>
                                        <input type="text" class="form-control" id="inputFirstName" name="first_name"
                                            value="" placeholder="First name" required>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="inputLastName">Last name</label>
                                        <input type="text" class="form-control" id="inputLastName" name="last_name"
                                            value="" placeholder="Last name">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value=""
                                        placeholder="Email" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password <a class="bs-tooltip" href="javascrip:void(0)"
                                            data-bs-placement="right"
                                            title="A minimum of 8 characters, and should contain at least 1 uppercase, 1 number, 1 special character."><i
                                                class="fa-solid fa-circle-exclamation"></i></a></label>
                                    <input type="password" class="form-control" id="password" name="password"
                                        value="" placeholder="Password" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control phone-mask" id="phone" name="phone"
                                        value="" placeholder="Phone" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Zip Code</label>
                                    <input type="text" class="form-control" id="zipcode" name="zipcode" value=""
                                        placeholder="zipcode" required>
                                </div>
                            </div>

                            <div class="card-footer text-center">
                                <button type="submit" class="btn btn-sm btn-primary save-btn">Save User</button>
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
            let rules = {
                phone: {
                    required: true,
                    phoneNo: true
                },
                zipcode: {
                    required: true,
                    postalCode: true
                }
            }
            $('.save-btn').on('click', function(e) {
                e.preventDefault();
                const button = $(this);
                button.prop('disabled', true);
                let validator = validateForm($('form[name="createUser"]'), rules, {})
                let url = $('form[name="createUser"]').attr('action')
                let method = $('form[name="createUser"]').attr('method')

                if (validator.form()) {
                    let formElement = $('form[name="createUser"]')[0];
                    sanitizePhoneInputs(formElement);
                    let formData = new FormData(formElement);

                    $.ajax({
                        url: url,
                        method: method,
                        data: formData,
                        processData: false, // Prevent jQuery from processing the data
                        contentType: false, // Prevent jQuery from setting content type
                        success: function(response) {
                            swalAlert2(response.status, response.message, response.redirect)
                            setTimeout(function() {
                                window.location.href = response.redirect
                            }, 1800);
                        },
                        error: function(response) {
                            swalAlert(response.responseJSON.status, response.responseJSON
                                .errors, 1500)
                            button.prop('disabled', false);
                        },
                        complete: function() {
                            button.prop('disabled', false);
                        }
                    });
                } else {
                    button.prop('disabled', false);
                }
            })
        })
    </script>
@endpush
