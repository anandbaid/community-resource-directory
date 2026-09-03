@extends('backend.layouts.app')
@section('title', ' | Suggested Organizations')
@section('content')
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Suggested Organizations</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Suggested Organizations
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
                        <div class="card-header d-flex flex-wrap justify-content-between">
                            <h5 class="card-title mb-0">Suggested Organizations</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-striped admin-data-table">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ __('Image') }}</th>
                                        <th scope="col">{{ __('Name') }}</th>
                                        <th scope="col">{{ __('Email') }}</th>
                                        <th scope="col">{{ __('Status') }}</th>
                                        <th scope="col">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!$organizations->isEmpty())
                                        @foreach ($organizations as $organization)
                                            <tr>
                                                <td><img src="{{ $organization->logo == null ? asset('assets/img/placeholder.png') : asset($organization->logo) }}"
                                                        alt="Image" width="60" height="60"
                                                        class="profile-image-backend">
                                                </td>
                                                <td>{{ ucwords($organization->name) ?? '' }}</td>
                                                <td>{{ $organization->email ?? '' }}</td>
                                                <td>{{ ucfirst($organization->status) ?? '' }}</td>
                                                <td>
                                                    <a href="{{ route('admin.suggested-organizations.edit', $organization->id) }}"
                                                        class="btn btn-primary">
                                                        @if ($organization->status == 'pending')
                                                            <i class="fa-solid fa-pen"></i>
                                                        @else
                                                            <i class="fa-solid fa-eye"></i>
                                                        @endif
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5" class="text-center">No record found.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('custom-scripts')
    <script type="text/javascript">
        window.addEventListener('DOMContentLoaded', function() {

        });
    </script>
@endpush
