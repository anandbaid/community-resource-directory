@extends('backend.layouts.app')
@section('title', ' | View User Details')
@section('content')
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">View User</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            View User
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
                        <h4 class="card-title mt-2 mb-0 text-center">View User Details</h4>
                        <form name="editUser" id="editUser" class="px-3" action="" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="text-center">
                                    <img id="profile_pic_output" alt=""
                                        src="{{ asset(!empty($user->profile_pic) ? $user->profile_pic : 'assets/img/user-placeholder.png') }}"
                                        class="rounded-circle img-responsive mt-2" width="100" height="100">
                                </div>
                                <div class="row mt-5">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="inputFirstName">First name</label>
                                        <input type="text" class="form-control" id="inputFirstName" name="first_name"
                                            value="{{ $user->first_name }}" placeholder="First name" disabled>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="inputLastName">Last name</label>
                                        <input type="text" class="form-control" id="inputLastName" name="last_name"
                                            value="{{ $user->last_name }}" placeholder="Last name" disabled>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="{{ $user->email }}" placeholder="Email" disabled>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control phone-mask" id="phone" name="phone" value="{{ \App\Http\Controllers\CommonFunction::formatPhone($user->phone) }}"
                                        placeholder="Phone" disabled>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Zip Code</label>
                                    <input type="tel" class="form-control" id="zipcode" name="zipcode"
                                        value="{{ $user->zipcode }}" placeholder="zipcode" disabled>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
