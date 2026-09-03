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
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <h5 class="card-title mb-0">Organizations</h5>
                            <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="mb-0" for="state-filter">State</label>
                                    <select id="state-filter" class="form-select form-select-sm" style="min-width: 180px;">
                                        <option value="">All</option>
                                        @foreach ($states as $state)
                                            <option value="{{ $state->name }}">{{ $state->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-danger me-1 disabled" id="bulk-delete-btn"
                                    data-url="{{ route('admin.organization.bulk-destroy') }}" disabled>
                                    Delete Selected
                                </button>
                                <a class="btn btn-primary"
                                    href="{{ route('admin.organization.create') }}">{{ __('Add New') }}</a>
                                <a class="btn btn-primary ms-1"
                                    href="{{ route('admin.organization.export') }}">{{ __('Export') }}</a>
                                <form id="manual-validate-form" class="d-inline" method="POST"
                                    action="{{ route('admin.organization.manual-validate') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-warning ms-1" id="manual-validate-button">
                                        Manual URL and email Verification
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="organization-table" class="table table-hover table-striped w-100">
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            <input type="checkbox" id="select-all-organizations">
                                        </th>
                                        <th scope="col">{{ __('Image') }}</th>
                                        <th scope="col">{{ __('Name') }}</th>
                                        <th scope="col">{{ __('Email') }}</th>
                                        <th scope="col">Report Count</th>
                                        <th scope="col">{{ __('Status') }}</th>
                                        <th scope="col">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
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
            const bulkBtn = $('#bulk-delete-btn');
            const selectAllCheckbox = $('#select-all-organizations');
            const organizationTable = $('#organization-table').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ordering: false,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50],
                    [10, 25, 50]
                ],
                ajax: {
                    url: "{{ route('admin.organization.list') }}",
                    type: 'GET',
                    data: function(d) {
                        d.state = $('#state-filter').val();
                    }
                },
                columns: [{
                        data: 'select',
                        name: 'select',
                        orderable: false,
                        searchable: false,
                        width: '40px'
                    },
                    {
                        data: 'image',
                        name: 'image',
                        orderable: false,
                        searchable: false,
                        width: '80px'
                    },
                    {
                        data: 'name',
                        name: 'name',
                    },
                    {
                        data: 'email',
                        name: 'email',
                    },
                    {
                        data: 'spam_count',
                        name: 'spam_count',
                        orderable: false,
                        searchable: false,
                        width: '120px'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        width: '120px'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        width: '200px'
                    },
                ],
                columnDefs: [{
                    targets: [0, 1, 2, 3, 4, 5, 6],
                    className: 'align-middle'
                }],
                drawCallback: function() {
                    selectAllCheckbox.prop('checked', false);
                    updateBulkButtonState();
                }
            });

            $('#state-filter').on('change', function() {
                organizationTable.ajax.reload(null, false);
            });

            const updateBulkButtonState = () => {
                const selectedCount = $('.org-select:checked').length;
                const isDisabled = selectedCount === 0;
                bulkBtn.prop('disabled', isDisabled);
                bulkBtn.toggleClass('disabled', isDisabled);
            };

            selectAllCheckbox.on('change', function() {
                $('.org-select').prop('checked', this.checked);
                updateBulkButtonState();
            });

            $(document).on('change', '.org-select', function() {
                const total = $('.org-select').length;
                const checked = $('.org-select:checked').length;
                if (checked === total) {
                    selectAllCheckbox.prop('checked', true);
                } else if (!this.checked) {
                    selectAllCheckbox.prop('checked', false);
                }
                updateBulkButtonState();
            });

            bulkBtn.on('click', function() {
                if (bulkBtn.prop('disabled')) {
                    return;
                }
                const selectedIds = $('.org-select:checked').map(function() {
                    return $(this).val();
                }).get();
                if (selectedIds.length === 0) {
                    return;
                }

                Swal.fire({
                    title: `Delete ${selectedIds.length} selected organization(s)?`,
                    text: 'This action could not be reverted',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (!result.value) {
                        return;
                    }
                    $.ajax({
                        url: bulkBtn.data('url'),
                        type: "DELETE",
                        data: {
                            _token: window.csrf_token,
                            ids: selectedIds
                        },
                        headers: {
                            'X-CSRF-TOKEN': window.csrf_token
                        },
                        dataType: "json",
                        success: function(response) {
                            swalAlert2('success', response.message, response.redirect)
                            setTimeout(function() {
                                organizationTable.ajax.reload(null, false);
                            }, 1500)
                        },
                        error: function(xhr) {
                            const msg = xhr?.responseJSON?.errors ||
                                'Something went wrong.';
                            swalAlert('error', msg, 1800)
                        }
                    });
                });
            });

            $(document).on('click', '#manual-validate-button', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Run validation for all organizations?',
                    text: 'This will validate URL and email for all records and may take a while.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, run now',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        $('#manual-validate-form').trigger('submit');
                    }
                });
            });
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
                                    organizationTable.ajax.reload(null, false);
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

            updateBulkButtonState();
        });
    </script>
@endpush
