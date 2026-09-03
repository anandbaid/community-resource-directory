@extends('backend.layouts.app')
@section('title', ' | View Organization Details')
@section('content')
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">View Organization</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            View Organization
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
                            <h4 class="card-title mb-0">View Organization Details</h4>
                        </div>
                        <form name="editOrganization" id="editOrganization" class="px-3" action="" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="row">
                                            <div class="text-center">
                                                <img id="organization_logo_output" alt=""
                                                    src="{{ isset($organization->logo) ? asset($organization->logo) : asset('assets/img/placeholder.png') }}"
                                                    class="img-responsive mt-2" width="120" height="120">
                                                <div class="mt-2">
                                                    <input type="file" class="d-none" name="organization_logo"
                                                        id="organization_logo" />
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
                                                            @foreach ($publications as $publication)
                                                                    <div><input type="checkbox" class="assign-publication mx-2"
                                                                            name="assign_publication[]"
                                                                            value="{{ $publication->id }}"
                                                                            {{ in_array($publication->id, $assignedPublicationIds ?? []) ? 'checked' : '' }}
                                                                            disabled>{{ $publication->title }}
                                                                    </div>
                                                            @endforeach
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
                                                    name="organization_name" value="{{ $organization->name }}"
                                                    placeholder="Organization name" disabled>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="organization_type">Organization Type</label>
                                                <select name="organization_type" id="organization_type" class="form-select"
                                                    disabled>
                                                    <option value="">Select Type</option>
                                                    <option value="government"
                                                        {{ $organization->type == 'government' ? 'selected' : '' }}>
                                                        Government</option>
                                                    <option value="non-government"
                                                        {{ $organization->type == 'non-government' ? 'selected' : '' }}>
                                                        Non-Government</option>
                                                </select>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email"
                                                    value="{{ $organization->email ?? '' }}" placeholder="Email" disabled>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Phone</label>
                                                <input type="tel" class="form-control phone-mask" id="phone" name="phone"
                                                    value="{{ \App\Http\Controllers\CommonFunction::formatPhone($organization->phone ?? '') }}"
                                                    placeholder="Phone" disabled>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label">Website</label>
                                                <input type="url" class="form-control" id="website" name="website"
                                                    value="{{ $organization->website ?? '' }}" placeholder="Website"
                                                    disabled>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6><u>Social Media</u></h6>
                                            </div>
                                            @php
                                                $social_links = json_decode($organizationDetails->social_links, true);
                                            @endphp
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label" for="facebook">Facebook</label>
                                                <input type="url" class="form-control" id="facebook"
                                                    name="facebook" value="{{ $social_links['facebook'] ?? '' }}"
                                                    placeholder="Facebook" disabled>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label" for="linkedin">Linkedin</label>
                                                <input type="url" class="form-control" id="linkedin"
                                                    name="linkedin" value="{{ $social_links['linkedin'] ?? '' }}"
                                                    placeholder="Linkedin" disabled>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label" for="instagram">Instagram</label>
                                                <input type="url" class="form-control" id="instagram"
                                                    name="instagram" value="{{ $social_links['instagram'] ?? '' }}"
                                                    placeholder="Instagram" disabled>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3">
                                                <label class="form-label">Program/Service Description</label>
                                                <textarea name="service_description" id="service_description" cols="30" rows="2" class="form-control"
                                                    maxlength="350" disabled>{{ $organizationDetails->service_description ?? '' }}</textarea>
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
                                                                {{ in_array($category->id, json_decode($organization->category ?? '', true) ?? []) ? 'checked' : '' }}
                                                                class="mx-1" disabled>{{ $category->name }}
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
                                                        {{ $organization->service_area_type == 'local' ? 'checked' : '' }}
                                                        disabled> Local
                                                    <input type="radio" name="service_area" value="state"
                                                        {{ $organization->service_area_type == 'state' ? 'checked' : '' }}
                                                        disabled> State
                                                    <input type="radio" name="service_area" value="national"
                                                        {{ $organization->service_area_type == 'national' ? 'checked' : '' }}
                                                        disabled>
                                                    National
                                                </div>
                                            </div>
                                            @if ($organization->service_area_type == 'state')
                                                <div class="mb-3 col-md-4 dynamic-field">
                                                    <label class="form-label">State</label>
                                                    <select name="service_state" id="service_state" class="form-select"
                                                        disabled>
                                                        <option value="">Select State</option>
                                                        @foreach ($states as $state)
                                                            <option value="{{ $state->name }}"
                                                                {{ $organization->service_area == $state->name ? 'selected' : '' }}>
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
                                                    <input type="radio" name="target_population" disabled
                                                        value="Adult"
                                                        {{ $organization->target_population == 'Adult' ? 'checked' : '' }}>
                                                    Adult
                                                    <input type="radio" name="target_population" disabled
                                                        value="Youth"
                                                        {{ $organization->target_population == 'Youth' ? 'checked' : '' }}>
                                                    Youth
                                                    <input type="radio" name="target_population" disabled
                                                        value="Justice Impacted"
                                                        {{ $organization->target_population == 'Justice Impacted' ? 'checked' : '' }}>
                                                    Justice Impacted
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="additional_resource">Additional
                                                    Resources</label>
                                                <input type="text" class="form-control" id="additional_resource"
                                                    name="additional_resource"
                                                    value="{{ $organizationDetails->additional_resource ?? '' }}"
                                                    placeholder="Additional Resources" disabled>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="title">Title</label>
                                                <input type="text" class="form-control" id="title" name="title"
                                                    value="{{ $organizationDetails->title ?? '' }}" placeholder="Title"
                                                    disabled>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" id="description" cols="30" rows="2" class="form-control" maxlength="250"
                                                    disabled>{{ $organizationDetails->description ?? '' }}</textarea>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label">File</label>
                                                @if (!empty($organizationDetails->file_url))
                                                    <div>
                                                        <a href="{{ url($organizationDetails->file_url) }}"
                                                            target="_blank">View file</a>
                                                    </div>
                                                @else
                                                    <div class="text-muted">No file uploaded.</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6><u>Point Of Contact</u></h6>
                                            </div>
                                            <div class="mb-3 col-md-2">
                                                <label class="form-label" for="website">Pronouns</label>
                                                <select name="point_of_contact_pronouns" id="pronouns"
                                                    class="form-select" disabled>
                                                    <option value="He/Him"
                                                        {{ $pointofcontacts->pronouns == 'He/Him' ? 'selected' : '' }}>
                                                        He/Him</option>
                                                    <option value="She/Her"
                                                        {{ $pointofcontacts->pronouns == 'She/Her' ? 'selected' : '' }}>
                                                        She/Her</option>
                                                    <option value="They/Them"
                                                        {{ $pointofcontacts->pronouns == 'They/Them' ? 'selected' : '' }}>
                                                        They/Them</option>
                                                </select>
                                            </div>
                                            <div class="mb-3 col-md-5">
                                                <label class="form-label" for="first_name">First Name</label>
                                                <input type="text" class="form-control" id="first_name"
                                                    name="point_of_contact_first_name"
                                                    value="{{ $pointofcontacts->first_name ?? '' }}"
                                                    placeholder="First Name" disabled>
                                            </div>
                                            <div class="mb-3 col-md-5">
                                                <label class="form-label" for="last_name">Last Name</label>
                                                <input type="text" class="form-control" id="last_name"
                                                    name="point_of_contact_last_name"
                                                    value="{{ $pointofcontacts->last_name ?? '' }}"
                                                    placeholder="Last Name" disabled>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" id="point_of_contact_email"
                                                    name="point_of_contact_email"
                                                    value="{{ $pointofcontacts->email ?? '' }}" placeholder="Email"
                                                    disabled>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Phone</label>
                                                <input type="tel" class="form-control phone-mask" id="point_of_contact_phone"
                                                    name="point_of_contact_phone"
                                                    value="{{ \App\Http\Controllers\CommonFunction::formatPhone($pointofcontacts->phone ?? '') }}" placeholder="Phone"
                                                    disabled>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label">Notes</label>
                                                <textarea name="point_of_contact_notes" id="notes" cols="30" rows="2" class="form-control"
                                                    maxlength="250" disabled>{{ $pointofcontacts->notes ?? '' }}</textarea>
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
                                                    value="{{ $organizationDetails->physical_address_1 ?? '' }}"
                                                    placeholder="Address Line 1" disabled>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="address_2">Address Line 2</label>
                                                <input type="text" class="form-control" id="address_2"
                                                    name="address_2"
                                                    value="{{ $organizationDetails->physical_address_2 ?? '' }}"
                                                    placeholder="Address Line 2" disabled>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">City</label>
                                                <input type="text" class="form-control" id="city" name="city"
                                                    value="{{ $organizationDetails->physical_city ?? '' }}"
                                                    placeholder="City" disabled>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">State</label>
                                                <select name="state" id="state" class="form-select" disabled>
                                                    <option value="">Select State</option>
                                                    @foreach ($states as $state)
                                                        <option value="{{ $state->name }}"
                                                            {{ $organizationDetails->physical_state == $state->name ? 'selected' : '' }}>
                                                            {{ $state->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">Postal Code</label>
                                                <input type="text" class="form-control" id="postcode"
                                                    name="postcode"
                                                    value="{{ $organizationDetails->physical_postal_code ?? '' }}"
                                                    placeholder="Postcode" disabled>
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
                                                    value="{{ $organizationDetails->mailing_address_1 ?? '' }}"
                                                    placeholder="Address Line 1" disabled>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="mailing_address_2">Address Line 2</label>
                                                <input type="text" class="form-control" id="mailing_address_2"
                                                    name="mailing_address_2"
                                                    value="{{ $organizationDetails->mailing_address_2 ?? '' }}"
                                                    placeholder="Address Line 2" disabled>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">City</label>
                                                <input type="text" class="form-control" id="mailing_city"
                                                    name="mailing_city"
                                                    value="{{ $organizationDetails->mailing_city ?? '' }}"
                                                    placeholder="City" disabled>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">State</label>
                                                <select name="mailing_state" id="mailing_state" class="form-select"
                                                    disabled>
                                                    <option value="">Select State</option>
                                                    @foreach ($states as $state)
                                                        <option value="{{ $state->name }}"
                                                            {{ $organizationDetails->mailing_state == $state->name ? 'selected' : '' }}>
                                                            {{ $state->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">Postal Code</label>
                                                <input type="text" class="form-control" id="mailing_postcode"
                                                    name="mailing_postcode"
                                                    value="{{ $organizationDetails->mailing_postal_code ?? '' }}"
                                                    placeholder="Postcode" disabled>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label d-block">
                                                    <h6><u>Source</u></h6>
                                                </label>
                                                <div class="radio-container">
                                                    <input type="radio" name="source" value="Website"
                                                        {{ $organizationDetails->source == 'Website' ? 'checked' : '' }}
                                                        disabled>
                                                    Website
                                                    <input type="radio" name="source" value="Search"
                                                        {{ $organizationDetails->source == 'Search' ? 'checked' : '' }}
                                                        disabled>
                                                    Search
                                                    <input type="radio" name="source" value="Referral"
                                                        {{ $organizationDetails->source == 'Referral' ? 'checked' : '' }}
                                                        disabled>
                                                    Referral
                                                    <input type="radio" name="source" value="Other"
                                                        {{ $organizationDetails->source == 'Other' ? 'checked' : '' }}
                                                        disabled>
                                                    Other
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
