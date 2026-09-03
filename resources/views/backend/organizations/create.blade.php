@extends('backend.layouts.app')

@section('title', ' | Create Organization')
@section('content')
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Create Organization</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Create Organization
                        </li>
                    </ol>
                </div>
            </div> <!--end::Row-->
        </div> <!--end::Container-->
    </div> <!--end::App Content Header-->


    <div class="app-content"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Organization Details</h4>
                        </div>
                        <form name="createOrganization" class="px-3" action="{{ route('admin.organization.store') }}"
                            method="POST" enctype="multipart/form-data" autocomplete="off">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="row">
                                            <div class="text-center">
                                                <img id="organization_logo_output" alt=""
                                                    src="{{ asset('assets/img/placeholder.png') }}"
                                                    class="img-responsive mt-2" width="120" height="120">
                                                <div class="mt-2">
                                                    <input type="file" class="d-none" name="organization_logo"
                                                        id="organization_logo" />
                                                    <button type="button" id="uploadLogo" class="btn btn-sm btn-primary"><i
                                                            class="fas fa-upload"></i>
                                                        Upload</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="accordion" id="assign-publication">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header"> <button class="form-select" type="button"
                                                            data-bs-toggle="collapse" data-bs-target="#collapseOne"
                                                            aria-expanded="true" aria-controls="collapseOne">
                                                            Assign Publication
                                                        </button> </h2>
                                                    <div id="collapseOne" class="accordion-collapse collapse"
                                                        data-bs-parent="#assign-publication">
                                                        <div class="accordion-body">
                                                            <button type="button"
                                                                class="btn btn-sm btn-primary add-publication">Add
                                                                New</button>
                                                            <hr>
                                                            <div class="publication-list">
                                                                @foreach ($publications as $publication)
                                                                    <div><input type="checkbox"
                                                                            class="assign-publication mx-2"
                                                                            name="assign_publication[]"
                                                                            value="{{ $publication->id }}">{{ $publication->title }}
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="organization_name">Organization Name</label>
                                                <input type="text" class="form-control" id="organization_name"
                                                    name="organization_name" value="" placeholder="Organization name"
                                                    required>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="organization_type">Organization Type</label>
                                                <select name="organization_type" id="organization_type" class="form-select"
                                                    required>
                                                    <option value="">Select Type</option>
                                                    <option value="government">Government</option>
                                                    <option value="non-government">Non-Government</option>
                                                </select>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email"
                                                    value="" placeholder="Email" required>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Phone</label>
                                                <input type="tel" class="form-control phone-mask" id="phone" name="phone" value="" placeholder="Phone" required>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label">Website</label>
                                                <input type="url" class="form-control" id="website" name="website"
                                                    value="" placeholder="Website" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6><u>Social Media</u></h6>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label" for="facebook">Facebook</label>
                                                <input type="url" class="form-control" id="facebook"
                                                    name="facebook" value="" placeholder="Facebook">
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label" for="linkedin">Linkedin</label>
                                                <input type="url" class="form-control" id="linkedin"
                                                    name="linkedin" value="" placeholder="Linkedin">
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label" for="instagram">Instagram</label>
                                                <input type="url" class="form-control" id="instagram"
                                                    name="instagram" value="" placeholder="Instagram">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3">
                                                <label class="form-label">Program/Service Description</label>
                                                <textarea name="service_description" id="service_description" cols="30" rows="2" class="form-control"
                                                    maxlength="350" required></textarea>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label d-block">
                                                    <h6><u>Service Categories</u></h6>
                                                </label>
                                                <div class="row checkbox-container">
                                                    @foreach ($categories as $category)
                                                        <div class="mb-1 col-md-3">
                                                            <input type="checkbox" name="service_categories[]"
                                                                value="{{ $category->id }}"
                                                                class="mx-1">{{ $category->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="mb-3 col-md-12 service-area">
                                                <label class="form-label d-block">
                                                    <h6><u>Service Areas</u></h6>
                                                </label>
                                                <div class="radio-container">
                                                    <input type="radio" name="service_area" value="local" required>
                                                    Local
                                                    <input type="radio" name="service_area" value="state" required>
                                                    State
                                                    <input type="radio" name="service_area" value="national" required>
                                                    National
                                                </div>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label d-block">
                                                    <h6><u>Target Population</u></h6>
                                                </label>
                                                <div class="radio-container">
                                                    <input type="radio" name="target_population" required
                                                        value="Adult">
                                                    Adult
                                                    <input type="radio" name="target_population" required
                                                        value="Youth">
                                                    Youth
                                                    <input type="radio" name="target_population" required
                                                        value="Justice Impacted">
                                                    Justice Impacted
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="additional_resource">Additional
                                                    Resources</label>
                                                <input type="text" class="form-control" id="additional_resource"
                                                    name="additional_resource" value=""
                                                    placeholder="Additional Resources">
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="title">Title</label>
                                                <input type="text" class="form-control" id="title" name="title"
                                                    value="" placeholder="Title">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" id="description" cols="30" rows="2" class="form-control" maxlength="250"></textarea>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="file_url">File</label>
                                                <input type="file" class="form-control" id="file_url"
                                                    name="file_url">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6><u>Point Of Contact</u></h6>
                                            </div>
                                            <div class="mb-3 col-md-2">
                                                <label class="form-label" for="website">Pronouns</label>
                                                <select name="point_of_contact_pronouns" id="pronouns"
                                                    class="form-select">
                                                    <option value="He/Him">He/Him</option>
                                                    <option value="She/Her">She/Her</option>
                                                    <option value="They/Them">They/Them</option>
                                                </select>
                                            </div>
                                            <div class="mb-3 col-md-5">
                                                <label class="form-label" for="first_name">First Name</label>
                                                <input type="text" class="form-control" id="first_name"
                                                    name="point_of_contact_first_name" value=""
                                                    placeholder="First Name">
                                            </div>
                                            <div class="mb-3 col-md-5">
                                                <label class="form-label" for="last_name">Last Name</label>
                                                <input type="text" class="form-control" id="last_name"
                                                    name="point_of_contact_last_name" value=""
                                                    placeholder="Last Name">
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" id="point_of_contact_email"
                                                    name="point_of_contact_email" value="" placeholder="Email">
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Phone</label>
                                                <input type="tel" class="form-control phone-mask" id="point_of_contact_phone"
                                                    name="point_of_contact_phone"
                                                    value="" placeholder="Phone">
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label">Notes</label>
                                                <textarea name="point_of_contact_notes" id="notes" cols="30" rows="2" class="form-control"
                                                    maxlength="250"></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6><u>Physical Address</u></h6>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="address_1">Address Line 1</label>
                                                <input type="text" class="form-control" id="address_1"
                                                    name="address_1" value="" placeholder="Address Line 1" required
                                                    autocomplete="off">

                                                {{-- address --}}
                                                <input type="hidden" id="latitude" name="latitude">
                                                <input type="hidden" id="longitude" name="longitude">
                                                {{-- address end --}}
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="address_2">Address Line 2</label>
                                                <input type="text" class="form-control" id="address_2"
                                                    name="address_2" value="" placeholder="Address Line 2">
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">City</label>
                                                <input type="text" class="form-control" id="city" name="city"
                                                    value="" placeholder="City" required>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">State</label>
                                                <select name="state" id="state" class="form-select" required>
                                                    <option value="">Select State</option>
                                                    @foreach ($states as $state)
                                                        <option value="{{ $state->name }}"> {{ $state->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">Postal Code</label>
                                                <input type="text" class="form-control" id="postcode"
                                                    name="postcode" value="" placeholder="Postcode" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6><u>Mailing Address</u></h6>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="mailing_address_1">Address Line 1</label>
                                                <input type="text" class="form-control" id="mailing_address_1"
                                                    name="mailing_address_1" value="" placeholder="Address Line 1">
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="mailing_address_2">Address Line 2</label>
                                                <input type="text" class="form-control" id="mailing_address_2"
                                                    name="mailing_address_2" value="" placeholder="Address Line 2">
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">City</label>
                                                <input type="text" class="form-control" id="mailing_city"
                                                    name="mailing_city" value="" placeholder="City">
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">State</label>
                                                <select name="mailing_state" id="mailing_state" class="form-select">
                                                    <option value="">Select State</option>
                                                    @foreach ($states as $state)
                                                        <option value="{{ $state->name }}"> {{ $state->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">Postal Code</label>
                                                <input type="text" class="form-control" id="mailing_postcode"
                                                    name="mailing_postcode" value="" placeholder="Postcode">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label d-block">
                                                    <h6><u>Source</u></h6>
                                                </label>
                                                <div class="radio-container">
                                                    <input type="radio" name="source" value="Website"> Website
                                                    <input type="radio" name="source" value="Search"> Search
                                                    <input type="radio" name="source" value="Referral"> Referral
                                                    <input type="radio" name="source" value="Other"> Other
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="mb-3">
                                                <label class="form-label">Is Member</label> <input type="checkbox"
                                                    name="is_member" value="1" class="mx-1">
                                            </div>
                                        </div>
                                    </div>
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
@push('modals')
    <div class="modal fade add-new-publication" id="add-new-publication" tabindex="-1"
        aria-labelledby="create-new-publication-label" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="create-new-publication-label">New Publication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="createPublication" name="createPublication" id="createPublication" method="POST"
                        action="{{ route('admin.publication.store') }}">
                        @csrf
                        @isset($organization)
                            <input type="hidden" name="organization_id" value="{{ $organization->id }}">
                        @endisset
                        <div class="form-group row mb-3 align-center">
                            <div class="col-md-3">
                                <label class="form-label">Title: </label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="title" name="title" value=""
                                    placeholder="Enter a Publication title" required>
                            </div>
                        </div>
                        <div class="form-group row mb-3 align-center">
                            <div class="col-md-3">
                                <label class="form-label">Publications by State: </label>
                            </div>
                            <div class="col-md-3">
                                <select name="state" id="state" class="form-select" required>
                                    <option value="">Select State</option>
                                    <option value="national">National</option>
                                    @foreach ($states as $state)
                                        <option value="{{ $state->id }}">
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
                                <textarea name="description" id="description" class="form-control ck-editor-content" cols="30" rows="10" required></textarea>
                            </div>
                        </div>
                        <div class="form-group row mb-3 align-center">
                            <div class="col-md-3">
                                <label class="form-label">Publication File: </label>
                            </div>
                            <div class="col-md-8">
                                <div class="mt-2">
                                    <input type="file" class="d-none" name="cover_file" id="cover_file" />
                                    <button type="button" id="uploadFile2" class="btn btn-primary"><i
                                            class="fas fa-upload"></i>
                                        Upload File</button>
                                </div>
                                <span id="file_name_display"></span>
                                <div id="cover_file_error"></div>
                            </div>
                        </div>
                        <div class="form-group row mb-3 align-center">
                            <div class="col-md-3">
                                <label class="form-label">Cover Image: </label>
                            </div>
                            <div class="col-md-8">
                                <img id="cover_image_output" alt=""
                                    src="{{ asset('/assets/img/placeholder.png') }}" class="img-responsive mt-2"
                                    width="128" height="128">
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
                                    name="publication_url" value="" placeholder="">
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-primary save-publication-btn">Save</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endpush
@push('custom-scripts')
    <script src="{{ asset('plugins/ckeditor/ckeditor.js') }}"></script>
    <script type="text/javascript">
        window.addEventListener('load', function() {
            $('#uploadLogo').on('click', function(e) {
                e.preventDefault();
                $(this).parent().children('input[name="organization_logo"]').click()
            })
            $('input[name="organization_logo"]').on('change', function(e) {
                const output = document.getElementById('organization_logo_output');
                output.src = URL.createObjectURL(e.target.files[0]);
                output.onload = function() {
                    URL.revokeObjectURL(output.src)
                }
            })

            // Assign Publication
            $(document).on('click', '.add-publication', function(e) {
                $('#add-new-publication').find('form')[0].reset();
                $('#add-new-publication').modal('show');
            })
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
                $(this).parent().children('input[name="cover_file"]').click();
            });

            $('input[name="cover_file"]').on('change', function(e) {
                const fileNameDisplay = document.getElementById(
                    'file_name_display'); // Element to show the file name
                const file = e.target.files[0];
                if (file) {
                    fileNameDisplay.textContent = file.name;
                }
            });
            $('.save-publication-btn').on('click', function(e) {
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
                if (formData.has('cover_file')) {
                    let file = formData.get('cover_file');
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
                if (validator.form() && coverImage && coverFile) {
                    $.ajax({
                        url: url,
                        method: method,
                        data: formData,
                        processData: false, // Prevent jQuery from processing the data
                        contentType: false, // Prevent jQuery from setting content type
                        success: function(response) {
                            // console.log(response)
                            let newData = `
                            <div><input type="checkbox" class="assign-publication mx-2"
                                name="assign_publication[]"
                                value="${response.data.id}" checked>${response.data.title}
                            </div>`;
                            $('.publication-list').append(newData)
                            $('#add-new-publication').modal('hide');
                        },
                        error: function(response) {
                            swalAlert(response.responseJSON.status, response.responseJSON
                                .errors, 4000)
                        }
                    });
                } else {
                    if (coverImage) {
                        $('#cover_image_error').empty().hide()
                    } else {
                        $('#cover_image_error').html("This field is required").show()
                    }
                    if (coverFile) {
                        $('#cover_file_error').empty().hide()
                    } else {
                        $('#cover_file_error').html("This field is required").show()
                    }
                }
            })

            const serviceAreaRadios = document.querySelectorAll('input[name="service_area"]');
            const formContainer = document.querySelector('.service-area'); // Parent container for the new field
            // Function to handle radio button click
            serviceAreaRadios.forEach(radio => {
                radio.addEventListener('click', () => {
                    // Remove any existing dynamically added field
                    const existingField = document.querySelector('.dynamic-field');
                    if (existingField) {
                        existingField.remove();
                    }

                    if (radio.value === 'state') {
                        // Add the dynamic field if "state" is selected
                        const dynamicField = document.createElement('div');
                        dynamicField.classList.add('mb-3', 'col-md-4', 'dynamic-field');
                        dynamicField.innerHTML = `
                        <label class="form-label">State</label>
                        <select name="service_state" id="service_state" class="form-select" required>
                            <option value="">Select State</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->name }}"> {{ $state->name }}</option>
                            @endforeach
                        </select>
                    `;
                        formContainer.insertAdjacentElement('afterend', dynamicField);
                    }
                });
            });

            // Form Validation
            let rules = {
                phone: {
                    required: true,
                    phoneNo: true
                },
                point_of_contact_phone: {
                    required: false,
                    phoneNo: true
                },
                point_of_contact_email: {
                    required: false,
                    email: true
                },
                postcode: {
                    required: true,
                    postalCode: true
                },
                mailing_postcode: {
                    required: false,
                    postalCode: true
                },
                facebook: {
                    required: false,
                    facebookUrl: true
                },
                linkedin: {
                    required: false,
                    linkedinUrl: true
                },
                instagram: {
                    required: false,
                    instagramUrl: true
                },
                twitter: {
                    required: false,
                    twitterUrl: true
                }
            }
            $('.save-btn').on('click', function(e) {
                e.preventDefault();
                const button = $(this);
                button.prop('disabled', true);
                let validator = validateForm($('form[name="createOrganization"]'), rules, {})
                let url = $('form[name="createOrganization"]').attr('action')
                let method = $('form[name="createOrganization"]').attr('method')
                // let formElement = $('form[name="createOrganization"]')[0];
                // let formData = new FormData(formElement);
                // console.log(formData);
                if (validator.form()) {
                    let formElement = $('form[name="createOrganization"]')[0];
                    sanitizePhoneInputs(formElement);
                    let formData = new FormData(formElement);

                    // console.log(formData)
                    $.ajax({
                        url: url,
                        method: method,
                        data: formData,
                        processData: false, // Prevent jQuery from processing the data
                        contentType: false, // Prevent jQuery from setting content type
                        success: function(response) {
                            // console.log(response)
                            swalAlert2(response.status, response.message, response.redirect)
                            setTimeout(function() {
                                window.location.href = response.redirect
                            }, 3000);
                        },
                        error: function(response) {
                            swalAlert(response.responseJSON.status, response.responseJSON
                                .errors, 7000)
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

    <!-- Include Google Maps Places API -->
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('custom.map_api_key') }}&libraries=places"></script>
    <script>
        function setIfExists(id, value) {
            var el = document.getElementById(id);
            if (el && value) {
                el.value = value;
            }
        }

        function setStateValue(stateValue, stateShort) {
            var select = document.getElementById('state');
            if (!select) {
                return;
            }
            var candidates = [];
            if (stateValue) {
                candidates.push(stateValue);
            }
            if (stateShort && stateShort !== stateValue) {
                candidates.push(stateShort);
            }
            if (candidates.length === 0) {
                return;
            }
            var options = Array.prototype.slice.call(select.options);
            var match = options.find(function(option) {
                return candidates.includes(option.value) || candidates.includes(option.text);
            });
            if (match) {
                select.value = match.value;
            }
        }

        function handlePlaceSelection(place) {
            if (!place || !place.geometry || !place.geometry.location) {
                return;
            }
            setIfExists('latitude', place.geometry.location.lat());
            setIfExists('longitude', place.geometry.location.lng());

            var components = place.address_components || [];
            var findComponent = function(type) {
                return components.find(function(component) {
                    return component.types.includes(type);
                });
            };

            var cityComponent = findComponent('locality') || findComponent('sublocality') || findComponent('administrative_area_level_2');
            var stateComponent = findComponent('administrative_area_level_1');
            var postalComponent = findComponent('postal_code');

            setIfExists('city', cityComponent ? cityComponent.long_name : '');
            setIfExists('postcode', postalComponent ? postalComponent.long_name : '');
            if (stateComponent) {
                setStateValue(stateComponent.long_name, stateComponent.short_name);
            }
        }

        function handleMailingPlaceSelection(place) {
            if (!place || !place.geometry) {
                return;
            }
            var components = place.address_components || [];
            var findComponent = function(type) {
                return components.find(function(component) {
                    return component.types.includes(type);
                });
            };

            var cityComponent = findComponent('locality') || findComponent('sublocality') || findComponent('administrative_area_level_2');
            var stateComponent = findComponent('administrative_area_level_1');
            var postalComponent = findComponent('postal_code');

            setIfExists('mailing_address_1', place.formatted_address || place.name);
            setIfExists('mailing_city', cityComponent ? cityComponent.long_name : '');
            setIfExists('mailing_postcode', postalComponent ? postalComponent.long_name : '');
            if (stateComponent) {
                var select = document.getElementById('mailing_state');
                if (select) {
                    var options = Array.prototype.slice.call(select.options);
                    var candidates = [stateComponent.long_name, stateComponent.short_name];
                    var match = options.find(function(option) {
                        return candidates.includes(option.value) || candidates.includes(option.text);
                    });
                    if (match) {
                        select.value = match.value;
                    }
                }
            }
        }

        function initialize() {
            var input = document.getElementById('address_1');
            if (!input) {
                return;
            }
            var autocomplete = new google.maps.places.Autocomplete(input, {
                componentRestrictions: {
                    country: 'us'
                },
            });

            autocomplete.addListener('place_changed', function() {
                handlePlaceSelection(autocomplete.getPlace());
            });
        }

        function initializeMailing() {
            var input = document.getElementById('mailing_address_1');
            if (!input) {
                return;
            }
            var autocomplete = new google.maps.places.Autocomplete(input, {
                componentRestrictions: {
                    country: 'us'
                },
            });

            autocomplete.addListener('place_changed', function() {
                handleMailingPlaceSelection(autocomplete.getPlace());
            });
        }

        google.maps.event.addDomListener(window, 'load', function() {
            initialize();
            initializeMailing();
        });
    </script>
@endpush
