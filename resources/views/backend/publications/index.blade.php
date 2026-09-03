@extends('backend.layouts.app')
@section('title', ' | Publications')
@push('custom-styles')
    <style>
        .lib-desc {
            max-width: 320px;
        }
        .lib-desc .clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 5;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
@endpush
@section('content')

    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Publications</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Publications
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
                            <h5 class="card-title mb-0">Publications</h5>
                            <a class="btn btn-primary float-end" href="{{ route('admin.publication.create') }}">Add New</a>
                            <br class="float-none">
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-striped admin-data-table">
                                <thead>
                                    <tr>
                                        <th scope="col">Cover File</th>
                                        <th scope="col">Publication Title</th>
                                        <th scope="col">state</th>
                                        <th scope="col" class="w-50">Description</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($publications) > 0)
                                        @foreach ($publications as $publication)
                                            <tr data-id="">
                                                <td><img src="{{ $publication->image == null ? asset('/assets/img/placeholder.png') : asset($publication->image) }}"
                                                        alt="Profile Image" width="60" height="60"
                                                        class="profile-image-backend">
                                                </td>
                                                <td>{{ $publication->title }}</td>
                                                <td>
                                                    @if ($publication->state)
                                                        {{ $publication->state == 'national' ? ucfirst($publication->state) : $getState($publication->state) }}
                                                    @endif
                                                </td>
                                                <td class="lib-desc">
                                                    <div class="clamp-2">{!! strip_tags($publication->description) !!}</div>
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.publication.show', $publication->id) }}"
                                                        class="btn btn-primary">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.publication.edit', $publication->id) }}"
                                                        class="btn btn-primary">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </a>
                                                    <button type="button" class="btn btn btn-danger delete-publication"
                                                        data-id="{{ $publication->id }}" data-type="delete"
                                                        data-url="{{ route('admin.publication.destroy', $publication->id) }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
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
            $('.delete-publication').on('click', function() {
                let element = $(this);
                let url = $(this).attr('data-url');
                Swal.fire({
                    title: 'Are you sure, you want to delete the publication?',
                    text: 'This action could not be reverted',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, do it!',
                    cancelButtonText: 'No, cancel!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: url,
                            type: "DELETE",
                            data: {
                                _token: window.csrf_token
                            },
                            headers: {
                                'X-CSRF-TOKEN': window.csrf_token
                            },
                            dataType: "json",
                            success: function(response) {
                                swalAlert('success', response.message)
                                setTimeout(function() {
                                    window.location.reload();
                                }, 3000)
                            },
                            error: function(xhr, textStatus, errorThrown) {
                                swalAlert(xhr.responseJSON.status, xhr.responseJSON
                                    .errors, 4000)
                            }
                        })
                    }
                });
            });
        });
    </script>
@endpush
