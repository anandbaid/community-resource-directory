@extends('backend.layouts.app')

@section('title', ' | Create Eamil Template')
@section('content')
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Create Template</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Create Template
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
                            <h4 class="card-title mb-0">Eamil Template Details</h4>
                        </div>

                        <form name="createEmailtemplate" id="createEmailtemplate" class="px-3"
                            action="{{ route('admin.emailtemplate.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Template Name: </label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" id="template-name" name="template_name"
                                            value="" placeholder="Template Name" required>
                                    </div>
                                </div>

                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Template Subject: </label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" id="template-title" name="template_title"
                                            value="" placeholder="Template Title" required>

                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Template Content: </label>
                                    </div>
                                    <div class="col-md-10">

                                        <textarea id="template-content" name="template_content" class="form-control ckeditor h-300 ck-editor-content"
                                            placeholder="{{ __('Enter') }}.." required></textarea>
                                        <div class="editor-error-message d-none">This field is required.</div>
                                    </div>
                                </div>
                                <div class="form-group row align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Template Data: </label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" id="template-data" name="template_data"
                                            value="" placeholder="Tempalte Data" autocomplete="off" required>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex ">
                                <button type="submit" class="btn btn btn-primary mr-2 save-btn" name="templateformsubmit"
                                    id="templateformsubmit" value="Submit">{{ __('Submit') }}</button>
                                <button type="reset" class="btn btn-danger mx-2">{{ __('Cancel') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('custom-scripts')
    <script src="{{ asset('plugins/ckeditor/ckeditor.js') }}"></script>
    <script>
        window.addEventListener('load', function() {
            $('.save-btn').on('click', function(e) {
                e.preventDefault();
                // Disable the button to prevent double-clicks
                const button = $(this);
                button.prop('disabled', true);
                let validator = validateForm($('form[name="createEmailtemplate"]'), {}, {})
                let url = $('#createEmailtemplate').attr('action')
                let method = $('#createEmailtemplate').attr('method')

                if (window.editors[0].editor) {
                    let editorData = window.editors[0].editor.getData();
                    if (editorData == "") {
                        $('.editor-error-message')?.addClass('editor-error-invalid').removeClass('d-none')
                    } else {
                        $('.editor-error-message')?.addClass('d-none').removeClass('editor-error-invalid')
                    }
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'ckeditor_data',
                        value: editorData
                    }).appendTo('form[name="createEmailtemplate"]');
                }

                if (validator.form() && window.editors[0].editor.getData() != "") {
                    let formData = $('form[name="createEmailtemplate"]').serialize();
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
