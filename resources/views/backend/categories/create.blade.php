@extends('backend.layouts.app')
@section('title', ' | ' . $type . ' Categories')
@section('content')

    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">{{ $type }} Category</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Categories</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $type }} Category
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
                            <h4 class="card-title mb-0 text-center">{{ $type }} Category</h4>
                        </div>
                        <div class="card-body">
                            <form name="createCategory" id="createCategory" class="px-3"
                                action="{{ $type == 'Edit' ? route('admin.category.update', $category->id) : route('admin.category.store') }}"
                                method="{{ $type == 'Edit' ? 'PATCH' : 'POST' }}">
                                @csrf
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-3">
                                        <label class="form-label">Category Name: </label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" id="name" required name="name"
                                            value="{{ $category->name ?? '' }}" placeholder="Enter a Category Name">
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-3">
                                        <label class="form-label">Status: </label>
                                    </div>
                                    <div class="col-md-2">
                                        <select required name="status" id="status" class="form-control">
                                            <option value="active"
                                                {{ isset($category->status) && $category->status == 'active' ? 'selected' : '' }}>
                                                Active</option>
                                            <option value="inactive"
                                                {{ isset($category->status) && $category->status == 'inactive' ? 'selected' : '' }}>
                                                Inactive
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-3">
                                        <label class="form-label">Order: </label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="number" class="form-control" id="category_order" required
                                            name="category_order" value="{{ $category->category_order ?? '' }}"
                                            placeholder="">
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <button type="submit" class="btn btn-primary save-btn">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('custom-scripts')
    <script>
        $('.save-btn').on('click', function(e) {
            e.preventDefault();
            let validator = validateForm($('form[name="createCategory"]'), {}, {})
            let url = $('#createCategory').attr('action')
            let method = $('#createCategory').attr('method')
            let formData = $('form[name="createCategory"]').serialize();
            if (validator.form()) {
                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    success: function(response) {
                        swalAlert2(response.status, response.message, response.redirect)
                        setTimeout(function() {
                            window.location.href = response.redirect
                        }, 3000);
                    },
                    error: function(response) {
                        swalAlert(response.responseJSON.status, response.responseJSON.errors, 4000)
                    }
                });
            }
        })
    </script>
@endpush
