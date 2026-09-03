@extends('backend.layouts.app')
@section('title', ' | Manage Saved Searches')
@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Manage Saved Searches</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Manage Saved Searches
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Saved Searches</h5>
                        </div>
                        <div class="card-body">
                            <table id="saved-search-table" class="table table-hover table-striped w-100">
                                <thead>
                                    <tr>
                                        <th scope="col">User</th>
                                        <th scope="col">Search Parameters</th>
                                        <th scope="col">Saved On</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
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
            $('#saved-search-table').DataTable({
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
                    url: "{{ route('admin.saved-searches.list') }}",
                    type: 'GET',
                },
                columns: [{
                        data: 'user',
                        name: 'user',
                    },
                    {
                        data: 'criteria',
                        name: 'criteria',
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        width: '180px'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        width: '80px'
                    },
                ],
                columnDefs: [{
                    targets: [0, 1, 2, 3],
                    className: 'align-middle'
                }],
                drawCallback: function() {
                    if (window.feather) {
                        window.feather.replace();
                    }
                }
            });

            $(document).on('click', '.delete-saved-search', function() {
                const btn = $(this);
                const url = btn.data('url');

                Swal.fire({
                    title: 'Delete this saved search?',
                    text: 'This will remove the saved search and its PDF file.',
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
                        url: url,
                        method: 'DELETE',
                        data: {
                            _token: window.csrf_token
                        },
                        success: function(response) {
                            Swal.fire('Deleted', response.message || 'Saved search deleted.', 'success');
                            $('#saved-search-table').DataTable().ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            const msg = xhr?.responseJSON?.errors || 'Something went wrong.';
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                });
            });
        });
    </script>
@endpush
