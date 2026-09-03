@extends('backend.layouts.app')
@section('title', ' | Users')
@section('content')
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Users</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Users
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
                            <h5 class="card-title mb-0">Users</h5>
                            <a class="btn btn-primary float-end"
                                href="{{ route('admin.user.create') }}">{{ __('Add New') }}</a>
                            <br class="float-none">
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
                                    @if (!$users->isEmpty())
                                        @foreach ($users as $user)
                                            <tr>
                                                <td><img src="{{ $user->profile_pic == null ? asset('assets/img/user-placeholder.png') : asset($user->profile_pic) }}"
                                                        alt="Profile Image" width="60" height="60"
                                                        class="profile-image-backend">
                                                </td>
                                                <td>{{ ucwords($user->name) ?? '' }}</td>
                                                <td>{{ $user->email ?? '' }}</td>
                                                <td>{{ ucfirst($user->status) ?? '' }}</td>
                                                <td>
                                                    <a href="{{ route('admin.user.show', $user->id) }}"
                                                        class="btn btn-primary"><i class="fa-solid fa-eye"></i></a>
                                                    <a href="{{ route('admin.user.edit', $user->id) }}"
                                                        class="btn btn-primary"><i class="fa-solid fa-pen"></i></a>
                                                    <button type="button" class="btn btn btn-danger delete-user"
                                                        data-id="{{ $user->id }}" data-type="delete"
                                                        data-url="{{ route('admin.user.destroy', $user->id) }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    @if ($user->status == 'active')
                                                        <button type="button" onclick="statusUpdate($(this))"
                                                            data-status="inactive" data-method="PATCH"
                                                            data-message="to inactive the user"
                                                            data-url="{{ route('admin.user.status', $user->id) }}"
                                                            class="btn btn-secondary" title="Inactive">
                                                            <i class="fas fa-lightbulb icon-spacer"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" onclick="statusUpdate($(this))"
                                                            data-status="active" data-method="PATCH"
                                                            data-message="to active the user"
                                                            data-url="{{ route('admin.user.status', $user->id) }}"
                                                            class="btn btn-warning" title="Active">
                                                            <i class="fas fa-lightbulb icon-spacer"></i>
                                                        </button>
                                                    @endif
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
            $(document).on('click', '.delete-user', function() {
                let element = $(this);
                let url = $(this).attr('data-url');
                Swal.fire({
                    title: 'Are you sure, you want to delete the user?',
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
                                swalAlert2('success', response.message, response
                                    .redirect)
                                setTimeout(function() {
                                    window.location.reload();
                                }, 1500)
                            },
                            error: function(xhr, textStatus, errorThrown) {
                                swalAlert(xhr.responseJSON.status, xhr.responseJSON
                                    .errors, 1800)
                            }
                        })
                    }
                });
            });
        });
    </script>
@endpush
