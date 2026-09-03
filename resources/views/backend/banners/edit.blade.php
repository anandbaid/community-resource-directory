@extends('backend.layouts.app')
@section('title', ' | Edit Banners')

@section('content')
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Edit Banner</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Edit Banner
                        </li>
                    </ol>
                </div>
            </div> <!--end::Row-->
        </div> <!--end::Container-->
    </div> <!--end::App Content Header-->


    <div class="app-content"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Banner Details</h4>
                        </div>

                        <form name="editBanners" id="editBanners" class="px-3"
                            action="{{ route('admin.banner.update', $bannerDetails->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Page Title: </label>
                                    </div>
                                    <div class="col-md-10">
                                        <select class="form-control" name="page_title" id="page_title">
                                            <option value="">Select Page</option>
                                            @foreach ($pages as $key => $page)
                                                <option value="{{ $key }}"
                                                    {{ $bannerDetails->page_title == $key ? 'selected' : '' }}>
                                                    {{ $key }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="home-page  {{ $bannerDetails->page_title == 'Home' ? '' : 'd-none' }}">
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Banner Heading: </label>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" id="banner_heading"
                                                name="banner_heading" value="{{ $bannerDetails->banner_heading ?? '' }}"
                                                placeholder="Banner Heading" required>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Banner Text: </label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea name="banner_text" class="form-control" placeholder="{{ __('Enter') }}.." required>{{ $bannerDetails->banner_text ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Banner Image: </label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="file" class="form-control" id="banner_image" name="banner_image"
                                            value="">
                                        @if (!empty($bannerDetails->image))
                                            <a href="{{ url($bannerDetails->image) }}" target="_blank">
                                                {{ url($bannerDetails->image) }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Banner Status: </label>
                                    </div>
                                    <div class="col-md-10">
                                        <select class="form-control" name="status" id="status">
                                            <option value="active"
                                                {{ $bannerDetails->status == 'active' ? 'selected' : '' }}>Active</option>
                                            <option
                                                value="inactive"{{ $bannerDetails->status == 'inactive' ? 'selected' : '' }}>
                                                Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Banner Order: </label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="number" class="form-control" id="banner_order" name="banner_order"
                                            value="{{ $bannerDetails->order ?? '' }}">
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex ">
                                <button type="submit" class="btn btn btn-primary mr-2 save-btn" name="templateformsubmit"
                                    id="templateformsubmit" value="Submit">{{ __('Submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('custom-scripts')
    <script>
        window.addEventListener('load', function() {
            $('#page_title').on('change', function() {
                if ($(this).val() == 'Home') {
                    $('.home-page').removeClass('d-none')
                } else {
                    $('.home-page').addClass('d-none')
                }
            })
            $('.save-btn').on('click', function(e) {
                e.preventDefault();
                // Disable the button to prevent double-clicks
                const button = $(this);
                button.prop('disabled', true);
                let validator = validateForm($('form[name="editBanners"]'), {}, {})
                let url = $('#editBanners').attr('action')
                let method = $('#editBanners').attr('method')
                if (validator.form()) {
                    let formElement = $('form[name="editBanners"]')[0];
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
                            }, 3000);
                        },
                        error: function(response) {
                            swalAlert(response.responseJSON.status, response.responseJSON
                                .errors, 4000)
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
        });
    </script>
@endpush
