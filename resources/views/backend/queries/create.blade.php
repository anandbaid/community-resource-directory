@extends('backend.layouts.app')
@section('title', ' | Query Details')
@section('content')

    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Query Details</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Query Details</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Query Details
                        </li>
                    </ol>
                </div>
            </div> <!--end::Row-->
        </div> <!--end::Container-->
    </div> <!--end::App Content Header-->

    <!-- /.content-header -->
    <div class="app-content"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row justify-content-center">
                <div class="col-lg-12 col-md-8 col-sm-10 col-10">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0 text-center">Query Details</h4>
                        </div>
                        <div class="card-body">
                            <form name="queryDetails" id="queryDetails" class="px-3" action="" method="POST">
                                @csrf
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-3">
                                        <label class="form-label">User First Name: </label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" id="name" disabled name="name"
                                            value="{{ $query->first_name ?? '' }}">
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-3">
                                        <label class="form-label">User Last Name: </label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" id="name" disabled name="name"
                                            value="{{ $query->last_name ?? '' }}">
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-3">
                                        <label class="form-label">Email: </label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" id="name" disabled name="name"
                                            value="{{ $query->email ?? '' }}">
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-3">
                                        <label class="form-label">Organization: </label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" id="name" disabled name="name"
                                            value="{{ $query->organization ?? '' }}">
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-3">
                                        <label class="form-label">Message: </label>
                                    </div>
                                    <div class="col-md-8">
                                        <textarea name="" class="form-control" cols="30" rows="10" disabled>{{ $query->message ?? '' }}</textarea>
                                    </div>
                                </div>
                                {{-- <div class="text-center mt-3">
                                    <button type="submit" class="btn btn-primary save-btn">Save</button>
                                </div> --}}
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('custom-scripts')
    <script></script>
@endpush
