@extends('backend.layouts.app')
@section('title', ' | ' . $type . ' Publication')
@section('content')

    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">{{ $type }} Publication</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Publications</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $type }} Publication
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
                            <h4 class="card-title mb-0 text-center">{{ $type }} Publication</h4>
                        </div>
                        <div class="card-body">
                            <form name="createPublication" id="createPublication" class="px-3"
                                action="{{ $type == 'Edit' ? route('admin.publication.update', $publication->id) : route('admin.publication.store') }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                @if ($type == 'Edit')
                                    @method('PUT')
                                @endif
                                <input type="hidden" id="type" value="{{ $type }}">
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-3">
                                        <label class="form-label">Publication title: </label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" id="title" name="title"
                                            value="{{ $publication->title ?? '' }}" placeholder="Enter a Publication title"
                                            required>
                                    </div>
                                </div>
                                @if (isset($organizations))
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-3">
                                            <label class="form-label">Organizations: </label>
                                        </div>
                                        <div class="col-md-3">
                                            <select name="organization_ids[]" id="organization"
                                                class="form-select organization-select" multiple>
                                                @foreach ($organizations as $organization)
                                                    <option value="{{ $organization->id }}"
                                                        {{ in_array($organization->id, $selectedOrganizationIds ?? []) ? 'selected' : '' }}>
                                                        {{ $organization->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-3">
                                        <label class="form-label">Publications by State: </label>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="state" id="state" class="form-select" required>
                                            <option value="">Select State</option>
                                            <option value="national"
                                                {{ isset($publication) && $publication->state == 'national' ? 'selected' : '' }}>
                                                National</option>
                                            @foreach ($states as $state)
                                                <option value="{{ $state->id }}"
                                                    {{ isset($publication) && $publication->state == $state->id ? 'selected' : '' }}>
                                                    {{ $state->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-3">
                                        <label class="form-label">Descriptions: </label>
                                    </div>
                                    <div class="col-md-8">
                                        <textarea name="description" id="description" class="form-control ck-editor-content" cols="30" rows="10"
                                            required>{{ $publication->description ?? '' }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-3">
                                        <label class="form-label">Publication File: </label>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="mt-2">
                                            <input type="file" class="d-none" name="publication_file"
                                                id="publication_file" />
                                            <button type="button" id="uploadFile2" class="btn btn-primary"><i
                                                    class="fas fa-upload"></i>
                                                Upload File</button>
                                        </div>
                                        <span
                                            id="file_name_display">{{ !empty($publication->file) ? url($publication->file) : '' }}</span>
                                        <div id="publication_file_error"></div>
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-3">
                                        <label class="form-label">Cover Image: </label>
                                    </div>
                                    <div class="col-md-8">
                                        <img id="cover_image_output" alt=""
                                            src="{{ asset(!empty($publication->image) ? $publication->image : '/assets/img/placeholder.png') }}"
                                            class="img-responsive mt-2" width="128" height="128">
                                        <div class="mt-2">
                                            <input type="file" class="d-none" name="cover_image" id="cover_image" />
                                            <button type="button" id="uploadFile" class="btn btn-primary"><i
                                                    class="fas fa-upload"></i>
                                                Upload</button>
                                        </div>
                                        <span id="cover_image_error"></span>
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-3">
                                        <label class="form-label">Publication Url: </label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="url" class="form-control" id="publication_url"
                                            name="publication_url" value="{{ $publication->url ?? '' }}" placeholder="">
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
    <script src="{{ asset('plugins/ckeditor/ckeditor.js') }}"></script>
    <script>
        window.addEventListener('load', function() {
            $('.organization-select').select2({
                minimumResultsForSearch: Infinity,
                width: 'resolve'
            });
            $('#uploadFile').on('click', function(e) {
                e.preventDefault();
                $(this).parent().children('input[name="cover_image"]').click()
            })
            $('input[name="cover_image"]').on('change', function(e) {
                const output = document.getElementById('cover_image_output');
                output.src = URL.createObjectURL(e.target.files[0]);
                output.onload = function() {
                    URL.revokeObjectURL(output.src) // free memory
                }
            })

            $('#uploadFile2').on('click', function(e) {
                e.preventDefault();
                $(this).parent().children('input[name="publication_file"]').click();
            });

            $('input[name="publication_file"]').on('change', function(e) {
                const fileNameDisplay = document.getElementById(
                    'file_name_display'); // Element to show the file name
                const file = e.target.files[0];
                if (file) {
                    fileNameDisplay.textContent = file.name;
                }
            });
        })
        $('.save-btn').on('click', function(e) {
            e.preventDefault();
            window.editors.forEach(function(entry) {
                if (entry && entry.element && entry.editor) {
                    entry.element.value = entry.editor.getData();
                }
            });
            let validator = validateForm($('form[name="createPublication"]'), {}, {})
            let url = $('#createPublication').attr('action')
            let method = $('#createPublication').attr('method')
            let formElement = $('form[name="createPublication"]')[0];
            let formData = new FormData(formElement);
            let coverFile = true;
            let coverImage = true;
            
            if ($('#type').val() == 'Create') {
                if (formData.has('publication_file')) {
                    let file = formData.get('publication_file');
                    if (file && file.size == 0)
                        coverFile = false;
                } else {
                    coverFile = false;
                }
                if (formData.has('cover_image')) {
                    let file = formData.get('cover_image');
                    if (file && file.size == 0)
                        coverImage = false;
                } else {
                    coverImage = false;
                }
            }
                 if (validator.form() && coverImage && coverFile) {
            $.ajax({
                url: url,
                method: method,
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    swalAlert2(response.status, response.message, response.redirect)
                    setTimeout(function() {
                        window.location.href = response.redirect
                    }, 3000);
                },
                error: function(response) {
                    if (response.responseJSON && response.responseJSON.errors) {
                        swalAlert(response.responseJSON.status, response.responseJSON.errors, 4000)
                    } else {
                        swalAlert('error', 'An error occurred while saving', 4000)
                    }
                }
            });
        }  else {
            if ($('#type').val() == 'Create') {
                if (coverImage) {
                    $('#cover_image_error').empty().hide()
                } else {
                    $('#cover_image_error').html("This field is required").show()
                }
                if (coverFile) {
                    $('#publication_file_error').empty().hide()
                } else {
                    $('#publication_file_error').html("This field is required").show()
                }
            }
        }
    })
    </script>
@endpush
