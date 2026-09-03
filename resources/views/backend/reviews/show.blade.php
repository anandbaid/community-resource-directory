@extends('backend.layouts.app')
@section('title', ' | View Review')
@push('custom-styles')
    <style>
        .bi-star-fill {
            color: #ffd018;
        }
    </style>
@endpush
@section('content')
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Review</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">View Review</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Review
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
                            <h4 class="card-title mb-0 text-center">Review</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group row mb-2 align-center">
                                <div class="col-md-12">
                                    <label class="form-label mx-1">User:
                                    </label>{{ ucWords($reviewDetails->userDetails->name) }}
                                </div>
                            </div>
                            <div class="form-group row mb-2 align-center">
                                <div class="col-md-12">
                                    <label class="form-label mx-1">Organization:
                                    </label>{{ $reviewDetails->organizationDetails->name }}
                                </div>
                            </div>
                            <div class="form-group row mb-2 align-center">
                                <div class="col-md-12">
                                    <label class="form-label mx-1">Agency/Organization Rating:</label>
                                    @for ($i = 1; $i <= $reviewDetails->rate; $i++)
                                        <i class="bi bi-star-fill"></i>
                                    @endfor
                                </div>
                            </div>
                            <div class="form-group row mb-3 align-center">
                                <div class="col-md-12">
                                    <label class="form-label">1. In which state, district, or territory do you currently
                                        reside?</label>
                                </div>
                                <div class="col-md-3">
                                    <select name="" id="" class="form-select" disabled>
                                        @foreach ($states as $states)
                                            <option value="{{ $states->name }}"
                                                {{ $details['states'] == $states->name ? 'selected' : '' }}>
                                                {{ $states->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row mb-3 align-center">
                                <div class="col-md-12">
                                    <label class="form-label">2. Are you a system impacted individual?</label>
                                </div>
                                <div class="col-md-3">
                                    <select name="" id="" class="form-select" disabled>
                                        <option value="Yes" {{ $details['system_impacted'] == true ? 'selected' : '' }}>
                                            Yes</option>
                                        <option value="No" {{ $details['system_impacted'] == false ? 'selected' : '' }}>
                                            No</option>
                                    </select>
                                </div>
                            </div>
                            @php
                                $legal_systems = [
                                    'Returning Citizen',
                                    'Family or Friend',
                                    'Concerned Citizen',
                                    'Program Staff',
                                    'Legal Representative',
                                    'Educator',
                                    'Other',
                                ];
                            @endphp
                            @if ($details['system_impacted'] == true)
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-12">
                                        <label class="form-label">please select the option below that best describes
                                            your connection to the criminal legal system.</label>
                                    </div>
                                    @foreach ($legal_systems as $legal_system)
                                        <div class="col-md-3">
                                            <input type="checkbox" name="" value="{{ $legal_system }}"
                                                {{ in_array($legal_system, $details['legal_system'] ?? []) ? 'checked' : '' }}
                                                class="mx-1" disabled>{{ $legal_system }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="form-group row mb-3 align-center">
                                <div class="col-md-12">
                                    <label class="form-label">3. Are you currently serving a term of supervision (e.g.
                                        probation, parole, supervised release, etc.)?</label>
                                </div>
                                <div class="col-md-3">
                                    <select name="" id="" class="form-select" disabled>
                                        <option value="Yes"
                                            {{ $details['term_of_supervision'] == true ? 'selected' : '' }}>Yes</option>
                                        <option value="No"
                                            {{ $details['term_of_supervision'] == false ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row mb-3 align-center">
                                <div class="col-md-12">
                                    <label class="form-label">4. Is your rating based on personal experience or third-party
                                        disclosure?</label>
                                </div>
                                <div class="col-md-3">
                                    <select name="" id="" class="form-select" disabled>
                                        <option value=""
                                            {{ $details['experience'] == 'Personal Experience' ? 'selected' : '' }}>
                                            Personal Experience</option>
                                        <option value=""
                                            {{ $details['experience'] == 'Third-Party Disclosure' ? 'selected' : '' }}>
                                            Third-Party Disclosure</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row mb-3 align-center">
                                <div class="col-md-12">
                                    <label class="form-label">5. On what date was your initial interaction with the
                                        agency/organization you're currently rating? If unsure, please provide as close an
                                        estimate as possible.</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" id="" name=""
                                        value="{{ $details['initial_interaction'] }}" disabled>
                                </div>
                            </div>

                            <div class="form-group row mb-3 align-center">
                                <div class="col-md-12">
                                    <label class="form-label">6. Did/does your involvement with this agency/organization
                                        include structured (i.e. in-person, virtual, or hybrid) programming or require your
                                        participation in structured classes (e.g. career readiness, victim impact,
                                        parenting, etc.)?</label>
                                </div>
                                <div class="col-md-3">
                                    <select name="" id="" class="form-select" disabled>
                                        <option value="Yes"
                                            {{ $details['structured_involvement'] == true ? 'selected' : '' }}>Yes</option>
                                        <option value="No"
                                            {{ $details['structured_involvement'] == false ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                            @if ($details['structured_involvement'] == true)
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-12">
                                        <label class="form-label">were you required to attend a minimum number of
                                            program sessions and/or actively engage in classroom activities to successfully
                                            complete enrollment requirements?</label>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="" id="" class="form-select" disabled>
                                            <option value="Yes"
                                                {{ $details['classroom_activities'] == true ? 'selected' : '' }}>Yes
                                            </option>
                                            <option value="No"
                                                {{ $details['classroom_activities'] == false ? 'selected' : '' }}>No
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            @endif

                            <div class="form-group row mb-3 align-center">
                                <div class="col-md-12">
                                    <label class="form-label">7. Was/is your involvement with this agency/organization
                                        mandated by the courts and/or probation/parole?</label>
                                </div>
                                <div class="col-md-3">
                                    <select name="" id="" class="form-select" disabled>
                                        <option value="Yes"
                                            {{ $details['mandated_by_the_courts'] == true ? 'selected' : '' }}>Yes</option>
                                        <option value="No"
                                            {{ $details['mandated_by_the_courts'] == false ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row mb-3 align-center">
                                <div class="col-md-12">
                                    <label class="form-label">8. Did you find the agency/organization details (e g. Name,
                                        Address, Description, Service Categories, etc.) provided to be accurate?</label>
                                </div>
                                <div class="col-md-3">
                                    <select name="" id="" class="form-select" disabled>
                                        <option value="Yes"
                                            {{ $details['accurate_details'] == true ? 'selected' : '' }}>Yes</option>
                                        <option value="No"
                                            {{ $details['accurate_details'] == false ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>

                            @if ($details['accurate_details'] == false)
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-12">
                                        <label class="form-label">briefly describe any details provided in their
                                            listing that you determined to be inaccurate or misleading during your
                                            agency/organization interaction.</label>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="text" class="form-control" id="" name=""
                                            value="{{ $details['details'] }}" disabled>
                                    </div>
                                </div>
                            @endif

                            <div class="form-group row mb-3 align-center">
                                <div class="col-md-12">
                                    <label class="form-label">9. Based on your personal experience with this
                                        agency/organization, would you recommend them to others?</label>
                                </div>
                                <div class="col-md-3">
                                    <select name="" id="" class="form-select" disabled>
                                        <option value="Yes" {{ $details['recommend'] == true ? 'selected' : '' }}>Yes
                                        </option>
                                        <option value="No" {{ $details['recommend'] == false ? 'selected' : '' }}>No
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
