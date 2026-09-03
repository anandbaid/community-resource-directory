@extends('backend.layouts.app')
@section('title', ' | View Publication')
@section('content')

    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">View Publication</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Publications</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            View Publication
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
                            <h4 class="card-title mb-0 text-center">View Publication</h4>
                        </div>
                        <div class="card-body">
                            <form name="createPublication" id="createPublication" class="px-3" action=""
                                method="POST">
                                @csrf
                                <input type="hidden" id="type" value="{{ $type }}">
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-3">
                                        <label class="form-label">Publication title: </label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" id="title" name="title"
                                            value="{{ $publication->title ?? '' }}" placeholder="Enter a Publication title"
                                            disabled>
                                    </div>
                                </div>
                                @if (isset($organizations))
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-3">
                                            <label class="form-label">Organizations: </label>
                                        </div>
                                        <div class="col-md-3">
                                            <select name="organization_ids[]" id="organization"
                                                class="form-select organization-select" multiple disabled>
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
                                        <select name="state" id="state" class="form-select" disabled>
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
                                        <textarea name="description" id="description" class="form-control" cols="30" rows="10" disabled>{{ $publication->description ?? '' }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-3">
                                        <label class="form-label">Publication File: </label>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="mt-2">
                                            <input type="file" class="d-none" name="cover_file" id="cover_file" />
                                        </div>
                                        <span
                                            id="file_name_display">{{ !empty($publication->file) ? url($publication->file) : '' }}</span>
                                        <div id="cover_file_error"></div>
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
                                        </div>
                                        <span id="cover_image_error"></span>
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-3">
                                        <label class="form-label">Publication Url: </label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="url" class="form-control" id="publication_url" disabled
                                            name="publication_url" value="{{ $publication->url ?? '' }}" placeholder="">
                                    </div>
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
        window.addEventListener('load', function() {
            $('.organization-select').select2({
                minimumResultsForSearch: Infinity,
                width: 'resolve'
            });
        })
    </script>
@endpush
