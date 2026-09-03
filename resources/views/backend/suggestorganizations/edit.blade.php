@extends('backend.layouts.app')
@section('title', ' | Edit Suggested Organization Details')
@section('content')
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Modify Suggested Organization</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Modify Suggested Organization
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
                            <h4 class="card-title mb-0">Modify Suggested Organization Details</h4>
                        </div>
                        <form name="editOrganization" id="editOrganization" class="px-3"
                            action="{{ route('admin.suggested-organizations.update', $suggestedOrganization['id']) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="row">
                                            <div class="text-center">
                                                <img id="organization_logo_output" alt=""
                                                    src="{{ isset($suggestedOrganization['logo']) ? asset($suggestedOrganization['logo']) : asset('assets/img/placeholder.png') }}"
                                                    class="img-responsive mt-2" width="120" height="120">
                                                <div class="mt-2">
                                                    @if ($disabled == '')
                                                        <input type="file" class="d-none" name="organization_logo"
                                                            id="organization_logo" />
                                                        <button type="button" id="uploadFile"
                                                            class="btn btn-sm btn-primary"><i class="fas fa-upload"></i>
                                                            Upload</button>
                                                    @endif
                                                    <input type="hidden" name="organization_logo_suggested"
                                                        value="{{ $suggestedOrganization['logo'] }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="mb-3 col-md-12 service-area">
                                                <label class="form-label d-block">
                                                    <h6><u>Suggestion Type</u></h6>
                                                </label>
                                                <div class="radio-container">
                                                    <input type="radio" name="suggestion_type" value="new"
                                                        {{ $suggestedOrganization['suggestion_type'] == 'new' ? 'checked' : '' }}
                                                        disabled> New Entry
                                                    <input type="radio" name="suggestion_type" value="existing"
                                                        {{ $suggestedOrganization['suggestion_type'] == 'existing' ? 'checked' : '' }}
                                                        disabled> Existing Organization
                                                </div>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="organization_name">Organization Name</label>
                                                <input type="text" class="form-control" id="organization_name"
                                                    name="organization_name" value="{{ $suggestedOrganization['name'] }}"
                                                    placeholder="Organization name" required {{ $disabled }}>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="organization_type">Organization Type</label>
                                                <select name="organization_type" id="organization_type" class="form-select"
                                                    required {{ $disabled }}>
                                                    <option value="">Select Type</option>
                                                    <option value="government"
                                                        {{ $suggestedOrganization['type'] == 'government' ? 'selected' : '' }}>
                                                        Government</option>
                                                    <option value="non-government"
                                                        {{ $suggestedOrganization['type'] == 'non-government' ? 'selected' : '' }}>
                                                        Non-Government</option>
                                                </select>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email"
                                                    value="{{ $suggestedOrganization['email'] ?? '' }}" placeholder="Email"
                                                    required {{ $disabled }}>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Phone</label>
                                                <input type="tel" class="form-control phone-mask" id="phone" name="phone"
                                                    value="{{ \App\Http\Controllers\CommonFunction::formatPhone($suggestedOrganization['phone'] ?? '') }}" placeholder="Phone"
                                                    required {{ $disabled }}>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label">Website</label>
                                                <input type="url" class="form-control" id="website" name="website"
                                                    value="{{ $suggestedOrganization['website'] ?? '' }}"
                                                    placeholder="Website" required {{ $disabled }}>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6><u>Social Media</u></h6>
                                            </div>
                                            @php
                                                $social_links = $organizationDetails['social_links'];
                                            @endphp
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label" for="facebook">Facebook</label>
                                                <input type="url" class="form-control" id="facebook"
                                                    name="facebook" value="{{ $social_links['facebook'] ?? '' }}"
                                                    placeholder="Facebook" {{ $disabled }}>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label" for="linkedin">Linkedin</label>
                                                <input type="url" class="form-control" id="linkedin"
                                                    name="linkedin" value="{{ $social_links['linkedin'] ?? '' }}"
                                                    placeholder="Linkedin" {{ $disabled }}>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label" for="instagram">Instagram</label>
                                                <input type="url" class="form-control" id="instagram"
                                                    name="instagram" value="{{ $social_links['instagram'] ?? '' }}"
                                                    placeholder="Instagram" {{ $disabled }}>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3">
                                                <label class="form-label">Program/Service Description</label>
                                                <textarea name="service_description" id="service_description" cols="30" rows="2" class="form-control"
                                                    maxlength="350" required {{ $disabled }}>{{ $organizationDetails['service_description'] ?? '' }}</textarea>
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
                                                                {{ in_array($category->id, json_decode($suggestedOrganization['category'] ?? '', true) ?? []) ? 'checked' : '' }}
                                                                class="mx-1" required
                                                                {{ $disabled }}>{{ $category->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="mb-3 col-md-12 service-area">
                                                <label class="form-label d-block">
                                                    <h6><u>Service Areas</u></h6>
                                                </label>
                                                <div class="radio-container">
                                                    <input type="radio" name="service_area" value="local"
                                                        {{ $suggestedOrganization['service_area_type'] == 'local' ? 'checked' : '' }}
                                                        required {{ $disabled }}> Local
                                                    <input type="radio" name="service_area" value="state"
                                                        {{ $suggestedOrganization['service_area_type'] == 'state' ? 'checked' : '' }}
                                                        required {{ $disabled }}> State
                                                    <input type="radio" name="service_area" value="national"
                                                        {{ $suggestedOrganization['service_area_type'] == 'national' ? 'checked' : '' }}
                                                        required {{ $disabled }}>
                                                    National
                                                </div>
                                            </div>
                                            @if ($suggestedOrganization['service_area_type'] == 'state')
                                                <div class="mb-3 col-md-4 dynamic-field">
                                                    <label class="form-label">State</label>
                                                    <select name="service_state" id="service_state" class="form-select"
                                                        required {{ $disabled }}>
                                                        <option value="">Select State</option>
                                                        @foreach ($states as $state)
                                                            <option value="{{ $state->name }}"
                                                                {{ $suggestedOrganization['service_area'] == $state->name ? 'selected' : '' }}>
                                                                {{ $state->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label d-block">
                                                    <h6><u>Target Population</u></h6>
                                                </label>
                                                <div class="radio-container">
                                                    <input type="radio" name="target_population" required
                                                        {{ $disabled }} value="Adult"
                                                        {{ $suggestedOrganization['target_population'] == 'Adult' ? 'checked' : '' }}>
                                                    Adult
                                                    <input type="radio" name="target_population" required
                                                        {{ $disabled }} value="Youth"
                                                        {{ $suggestedOrganization['target_population'] == 'Youth' ? 'checked' : '' }}>
                                                    Youth
                                                    <input type="radio" name="target_population" required
                                                        {{ $disabled }} value="Justice Impacted"
                                                        {{ $suggestedOrganization['target_population'] == 'Justice Impacted' ? 'checked' : '' }}>
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
                                                    placeholder="Additional Resources" {{ $disabled }}>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="title">Title</label>
                                                <input type="text" class="form-control" id="title" name="title"
                                                    value="" placeholder="Title" {{ $disabled }}>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" id="description" cols="30" rows="2" class="form-control" maxlength="250"
                                                    {{ $disabled }}></textarea>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="file_url">File Url</label>
                                                <input type="url" class="form-control" id="file_url"
                                                    name="file_url" value="" placeholder="File Url"
                                                    {{ $disabled }}>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6><u>Point Of Contact</u></h6>
                                            </div>
                                            <div class="mb-3 col-md-2">
                                                <label class="form-label" for="website">Pronouns</label>
                                                <select name="point_of_contact_pronouns" id="pronouns"
                                                    class="form-select" {{ $disabled }}>
                                                    <option value="He/Him"
                                                        {{ $pointofcontacts['pronouns'] == 'He/Him' ? 'selected' : '' }}>
                                                        He/Him</option>
                                                    <option value="She/Her"
                                                        {{ $pointofcontacts['pronouns'] == 'She/Her' ? 'selected' : '' }}>
                                                        She/Her</option>
                                                    <option value="They/Them"
                                                        {{ $pointofcontacts['pronouns'] == 'They/Them' ? 'selected' : '' }}>
                                                        They/Them</option>
                                                </select>
                                            </div>
                                            <div class="mb-3 col-md-5">
                                                <label class="form-label" for="first_name">First Name</label>
                                                <input type="text" class="form-control" id="first_name"
                                                    name="point_of_contact_first_name"
                                                    value="{{ $pointofcontacts['first_name'] ?? '' }}"
                                                    placeholder="First Name" {{ $disabled }}>
                                            </div>
                                            <div class="mb-3 col-md-5">
                                                <label class="form-label" for="last_name">Last Name</label>
                                                <input type="text" class="form-control" id="last_name"
                                                    name="point_of_contact_last_name"
                                                    value="{{ $pointofcontacts['last_name'] ?? '' }}"
                                                    placeholder="Last Name" {{ $disabled }}>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" id="point_of_contact_email"
                                                    name="point_of_contact_email"
                                                    value="{{ $pointofcontacts['email'] ?? '' }}" placeholder="Email"
                                                    {{ $disabled }}>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Phone</label>
                                                <input type="tel" class="form-control phone-mask" id="point_of_contact_phone"
                                                    name="point_of_contact_phone"
                                                    value="{{ \App\Http\Controllers\CommonFunction::formatPhone($pointofcontacts['phone'] ?? '') }}" placeholder="Phone"
                                                    {{ $disabled }}>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label">Notes</label>
                                                <textarea name="point_of_contact_notes" id="notes" cols="30" rows="2" class="form-control"
                                                    maxlength="250" {{ $disabled }}>{{ $pointofcontacts['notes'] ?? '' }}</textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6><u>Physical Address</u></h6>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="address_1">Address Line 1</label>
                                                <input type="text" class="form-control" id="address_1"
                                                    name="address_1"
                                                    value="{{ $organizationDetails['physical_address_1'] ?? '' }}"
                                                    placeholder="Address Line 1" required {{ $disabled }} autocomplete="off">
                                                {{-- address --}}
                                                <input type="hidden" id="latitude" name="latitude" value="{{ $organizationDetails['latitude'] ?? '' }}">
                                                <input type="hidden" id="longitude" name="longitude" value="{{ $organizationDetails['longitude'] ?? '' }}">
                                                {{-- address end --}}
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="address_2">Address Line 2</label>
                                                <input type="text" class="form-control" id="address_2"
                                                    name="address_2"
                                                    value="{{ $organizationDetails['physical_address_2'] ?? '' }}"
                                                    placeholder="Address Line 2" {{ $disabled }}>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">City</label>
                                                <input type="text" class="form-control" id="city" name="city"
                                                    value="{{ $organizationDetails['physical_city'] ?? '' }}"
                                                    placeholder="City" required {{ $disabled }}>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">State</label>
                                                <select name="state" id="state" class="form-select" required
                                                    {{ $disabled }}>
                                                    <option value="">Select State</option>
                                                    @foreach ($states as $state)
                                                        <option value="{{ $state->name }}"
                                                            {{ $organizationDetails['physical_state'] == $state->name ? 'selected' : '' }}>
                                                            {{ $state->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">Postal Code</label>
                                                <input type="text" class="form-control" id="postcode"
                                                    name="postcode"
                                                    value="{{ $organizationDetails['physical_postal_code'] ?? '' }}"
                                                    placeholder="Postcode" required {{ $disabled }}>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6><u>Mailing Address</u></h6>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="mailing_address_1">Address Line 1</label>
                                                <input type="text" class="form-control" id="mailing_address_1"
                                                    name="mailing_address_1"
                                                    value="{{ $organizationDetails['mailing_address_1'] ?? '' }}"
                                                    placeholder="Address Line 1" {{ $disabled }}>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="mailing_address_2">Address Line 2</label>
                                                <input type="text" class="form-control" id="mailing_address_2"
                                                    name="mailing_address_2"
                                                    value="{{ $organizationDetails['mailing_address_2'] ?? '' }}"
                                                    placeholder="Address Line 2" {{ $disabled }}>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">City</label>
                                                <input type="text" class="form-control" id="mailing_city"
                                                    name="mailing_city"
                                                    value="{{ $organizationDetails['mailing_city'] ?? '' }}"
                                                    placeholder="City" {{ $disabled }}>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">State</label>
                                                <select name="mailing_state" id="mailing_state" class="form-select"
                                                    {{ $disabled }}>
                                                    <option value="">Select State</option>
                                                    @foreach ($states as $state)
                                                        <option value="{{ $state->name }}"
                                                            {{ $organizationDetails['mailing_state'] == $state->name ? 'selected' : '' }}>
                                                            {{ $state->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">Postal Code</label>
                                                <input type="text" class="form-control" id="mailing_postcode"
                                                    name="mailing_postcode"
                                                    value="{{ $organizationDetails['mailing_postal_code'] ?? '' }}"
                                                    placeholder="Postcode" {{ $disabled }}>
                                            </div>
                                        </div>
                                        <div class="row publication-container">
                                            <div class="col-md-12 d-flex justify-content-between align-items-center">
                                                <h6><u>Publications</u></h6>
                                                @if ($disabled == '')
                                                    <button type="button" id="add-publication"
                                                        class="btn btn-success mt-3">Add
                                                        More</button>
                                                @endif
                                            </div>
                                            @foreach ($publications ?? [] as $key => $publication)
                                                <div class="publication-item">
                                                    <hr class="hr-line">
                                                    <div class="mb-3 col-md-12">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <label class="form-label">Title</label>
                                                            @if ($disabled == '')
                                                                <button type="button"
                                                                    class="btn btn-danger remove-publication">Remove</button>
                                                            @endif
                                                        </div>
                                                        <input type="text" class="form-control"
                                                            name="publication_title[{{ $key }}]"
                                                            value="{{ $publication['publication_title'] ?? '' }}"
                                                            placeholder="Title" {{ $disabled }}>
                                                        <input type="hidden"
                                                            name="publication_update_existing[{{ $key }}]"
                                                            value="{{ $publication['update_existing'] ?? 0 }}">
                                                    </div>
                                                    <div class="mb-3 col-md-12">
                                                        <label class="form-label">Publications by State</label>
                                                        <select name="publication_state[{{ $key }}]"
                                                            class="form-select" {{ $disabled }}>
                                                            <option value="">Select State</option>
                                                            <option value="national"
                                                                {{ ($publication['publication_state'] ?? '') === 'national' ? 'selected' : '' }}>
                                                                National</option>
                                                            @foreach ($states as $state)
                                                                <option value="{{ $state->id }}"
                                                                    {{ (string) ($publication['publication_state'] ?? '') === (string) $state->id ? 'selected' : '' }}>
                                                                    {{ $state->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-3 col-md-12">
                                                        <label class="form-label">Description</label>
                                                        <textarea name="publication_description[{{ $key }}]" cols="30" rows="2" class="form-control"
                                                            maxlength="250" {{ $disabled }}>{{ $publication['publication_description'] ?? '' }}</textarea>
                                                    </div>
                                                    <div class="form-group row mb-3 align-center">
                                                        <label class="form-label">Publication File: </label>
                                                        <div class="mt-2 cover_file_container">
                                                            <input type="file" class="d-none cover-file"
                                                                name="cover_file[{{ $key }}]" />
                                                            @if ($disabled == '')
                                                                <button type="button"
                                                                    class="btn btn-primary cover-file-btn"><i
                                                                        class="fas fa-upload"></i> Upload File</button>
                                                            @endif
                                                            <input type="hidden"
                                                                name="cover_file_path[{{ $key }}]"
                                                                value="{{ $publication['cover_file'] }}">
                                                            <div>
                                                                <span
                                                                    class="file-name-display">{{ !empty($publication['cover_file']) ? url($publication['cover_file']) : '' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row mb-3 align-center">
                                                        <label class="form-label">Cover Image: </label>
                                                        <div class="mt-2 cover_file_container">
                                                            <input type="file" class="d-none cover-image"
                                                                name="cover_image[{{ $key }}]" />
                                                            @if ($disabled == '')
                                                                <button type="button"
                                                                    class="btn btn-primary cover-image-btn"><i
                                                                        class="fas fa-upload"></i> Upload Image</button>
                                                            @endif
                                                            <input type="hidden"
                                                                name="cover_image_path[{{ $key }}]"
                                                                value="{{ $publication['cover_image'] ?? '' }}">
                                                            <div>
                                                                <span
                                                                    class="file-name-display">{{ !empty($publication['cover_image']) ? url($publication['cover_image']) : '' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="row">
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label d-block">
                                                    <h6><u>Source</u></h6>
                                                </label>
                                                <div class="radio-container">
                                                    <input type="radio" name="source" value="Website"
                                                        {{ $organizationDetails['source'] == 'Website' ? 'checked' : '' }}
                                                        {{ $disabled }}>
                                                    Website
                                                    <input type="radio" name="source" value="Search"
                                                        {{ $organizationDetails['source'] == 'Search' ? 'checked' : '' }}
                                                        {{ $disabled }}>
                                                    Search
                                                    <input type="radio" name="source" value="Referral"
                                                        {{ $organizationDetails['source'] == 'Referral' ? 'checked' : '' }}
                                                        {{ $disabled }}>
                                                    Referral
                                                    <input type="radio" name="source" value="Other"
                                                        {{ $organizationDetails['source'] == 'Other' ? 'checked' : '' }}
                                                        {{ $disabled }}>
                                                    Other
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer text-center">
                                @if ($disabled == '')
                                    <button type="submit" class="btn btn-sm btn-success save-btn"
                                        data-name="accepted">Accept</button>
                                    <button type="submit" class="btn btn-sm btn-danger save-btn"
                                        data-name="rejected">Reject</button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="publication-item clone-publication d-none">
        <hr class="hr-line">
        <div class="mb-3 col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label">Title</label>
                @if ($disabled == '')
                    <button type="button" class="btn btn-danger remove-publication">Remove</button>
                @endif
            </div>
            <input type="text" class="form-control" name="publication_title[]" data-name="publication_title"
                value="" placeholder="Title">
            <input type="hidden" name="publication_update_existing[]" data-name="publication_update_existing"
                value="0">
        </div>
        <div class="mb-3 col-md-12">
            <label class="form-label">Publications by State</label>
            <select name="publication_state[]" class="form-select">
                <option value="">Select State</option>
                <option value="national">National</option>
                @foreach ($states as $state)
                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3 col-md-12">
            <label class="form-label">Description</label>
            <textarea name="publication_description[]" cols="30" rows="2" class="form-control"
                data-name="publication_description" maxlength="250"></textarea>
        </div>
        <div class="form-group row mb-3 align-center">
            <label class="form-label">Publication File: </label>
            <div class="mt-2 cover_file_container">
                <input type="file" class="d-none cover-file" name="cover_file[]" data-name="cover_file" />
                <button type="button" class="btn btn-primary cover-file-btn"><i class="fas fa-upload"></i> Upload
                    File</button>
                <input type="hidden" name="cover_file_path[]" data-name="cover_file_path" value="">
                <div>
                    <span class="file-name-display"></span>
                </div>
            </div>
        </div>
        <div class="form-group row mb-3 align-center">
            <label class="form-label">Cover Image: </label>
            <div class="mt-2 cover_file_container">
                <input type="file" class="d-none cover-image" name="cover_image[]" data-name="cover_image" />
                <button type="button" class="btn btn-primary cover-image-btn"><i class="fas fa-upload"></i> Upload
                    Image</button>
                <input type="hidden" name="cover_image_path[]" data-name="cover_image_path" value="">
                <div>
                    <span class="file-name-display"></span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-scripts')
    <script>
        window.addEventListener('load', function() {
            $('#uploadFile').on('click', function(e) {
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
                }
            };
            // Add more publications
            $('#add-publication').on('click', function() {
                // Clone the first .publication-item
                const newPublication = $('.clone-publication:first').clone();

                // Clear the input and textarea values
                newPublication.find('input[type="text"]').val('');
                newPublication.find('textarea').val('');
                newPublication.find('.file-name-display').html('');
                newPublication.find('input[type="file"]').val('');
                newPublication.removeClass('d-none')

                let lastIndex = 0;
                $('.publication-container .publication-item').find('input, textarea').each(function() {
                    const name = $(this).attr('name');
                    const match = name ? name.match(/\[(\d+)\]/) : null;
                    if (match) {
                        const currentIndex = parseInt(match[1], 10);
                        if (currentIndex > lastIndex) {
                            lastIndex = currentIndex;
                        }
                    }
                });

                const nextIndex = lastIndex + 1;

                // Update names with the next index
                newPublication.find('input, textarea').each(function() {
                    const baseName = $(this).attr(
                        'data-name'); // Use a data attribute for the base name
                    if (baseName) {
                        // Update the name to use the next index
                        const newName = `${baseName}[${nextIndex}]`;
                        $(this).attr('name', newName);
                        const newId = `${baseName}_${nextIndex}`;
                        $(this).attr('id', newId);
                    }
                });

                // Append the cloned item to the container
                $('.publication-container').append(newPublication);
            });

            // Remove a publication item
            $(document).on('click', '.remove-publication', function() {
                $(this).closest('.publication-item').remove();
            });
            $(document).on('click', '.cover-file-btn', function(e) {
                e.preventDefault();
                $(this).siblings('.cover-file').click();
            });
            $(document).on('click', '.cover-image-btn', function(e) {
                e.preventDefault();
                $(this).siblings('.cover-image').click();
            });

            $(document).on('change', '.cover-file, .cover-image', function(e) {
                const fileNameDisplay = $(this).closest('.cover_file_container').find('.file-name-display');
                const file = e.target.files[0];
                if (file) {
                    fileNameDisplay.html(file.name);
                }
            });


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
                        <select name="service_state" id="service_state" class="form-select" required {{ $disabled }}>
                            <option value="">Select State</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->name }}" {{ $suggestedOrganization['service_area_type'] == 'state' && $suggestedOrganization['service_area'] == $state->name ? 'selected' : '' }}> {{ $state->name }}</option>
                            @endforeach
                        </select>
                    `;
                        formContainer.insertAdjacentElement('afterend', dynamicField);
                    }
                });
            });


            $('.save-btn').on('click', function(e) {
                e.preventDefault();
                const button = $(this);
                button.prop('disabled', true);
                let validator = validateForm($('form[name="editOrganization"]'), rules, {})
                let url = $('form[name="editOrganization"]').attr('action')
                let method = $('form[name="editOrganization"]').attr('method')
                if (validator.form()) {
                    let formElement = $('form[name="editOrganization"]')[0];
                    sanitizePhoneInputs(formElement);
                    let formData = new FormData(formElement);
                    formData.append('form_type', button.attr('data-name'));
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
                            button.prop('$disabled', false);
                        },
                        complete: function() {
                            button.prop('$disabled', false);
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

            autocomplete.addListener('place_changed', function () {
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

            autocomplete.addListener('place_changed', function () {
                handleMailingPlaceSelection(autocomplete.getPlace());
            });
        }

        google.maps.event.addDomListener(window, 'load', function () {
            initialize();
            initializeMailing();
        });
    </script>
@endpush
