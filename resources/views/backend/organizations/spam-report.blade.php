@extends('backend.layouts.app')
@section('title', ' | Organizations')
@section('content')
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Organizations</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Organizations
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
                            <h5 class="card-title mb-0">Spam Report by user</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-striped admin-data-table">
                                <thead>
                                    <tr>
                                        <th scope="col">User Details</th>
                                        <th scope="col">Organization Details</th>
                                        <th scope="col">Reason</th>
                                        <th scope="col">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!$spamreport->isEmpty())
                                        @foreach ($spamreport as $spam_list)
                                            <tr>
                                                <td>
                                                    {{ ucwords($spam_list->user_name) ?? '' }}
                                                    <div><i class="fa-solid fa-envelope"></i>: {{ ucwords($spam_list->user_email) ?? '' }}</div>
                                                </td>
                                                <td>
                                                    {{ ucwords($spam_list->org_name) ?? '' }}
                                                    <div>Status: {{ ucfirst($spam_list->org_status) ?? '' }}</div>
                                                </td>
                                                <td>{{ $spam_list->spam_reason ?? '' }}</td>
                                                <td>
                                                    <a href="{{ route('admin.organization.edit', $spam_list->org_id) }}" title="Edit Organization"
                                                        class="btn btn-primary"> <i class="fa-solid fa-landmark"></i> </a>
                                                    <a href="{{ route('admin.user.edit', $spam_list->user_id) }}" title="Edit User"
                                                        class="btn btn-primary"> <i class="fa-solid fa-user"></i></a>
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
            $(document).on('click', '.delete-organization', function() {
                let element = $(this);
                let url = $(this).attr('data-url');
                Swal.fire({
                    title: 'Are you sure, you want to delete the organization?',
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
