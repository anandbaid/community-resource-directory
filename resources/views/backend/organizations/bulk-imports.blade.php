@extends('backend.layouts.app')
@section('title', ' | Bulk Imports')
@push('custom-styles')
    <style>
        .import-loader {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .import-loader.d-none {
            display: none !important;
        }
    </style>
@endpush
@section('content')
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Bulk Imports</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Bulk Imports
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
                    <div class="card position-relative">
                        <div class="import-loader d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div class="card-header">
                            <h4 class="card-title mb-0">Import Organizations</h4>
                        </div>
                        <form name="bulkImportOrganization" class="px-3" action="{{ route('admin.bulk-import-submit') }}"
                            method="POST" enctype="multipart/form-data" autocomplete="off">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="uploaded_file">Upload XLS File</label>
                                                <input type="file" class="form-control" id="uploaded_file"
                                                    name="uploaded_file" value="" required>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <button type="submit" class="btn btn-sm btn-primary save-btn">Upload</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Last Imported Response</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">Status</th>
                                        <th scope="col">Total Records</th>
                                        <th scope="col">Imported Records</th>
                                        <th scope="col">Error</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($responseData ?? []))
                                        <tr>
                                            <td>{{ ucfirst($responseData['status']) ?? '' }}</td>
                                            <td>{{ $responseData['total'] ?? '' }}</td>
                                            <td>{{ $responseData['imported'] ?? '' }}</td>
                                            <td>
                                                @if (count($responseData['error']))
                                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                        data-bs-target="#view-errors">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center">No records found</td>
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
@push('modals')
    <div class="modal fade view-errors" id="view-errors" tabindex="-1" aria-labelledby="view-errors-label"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="view-errors-label">Errors</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @foreach ($responseData['error'] ?? [] as $key => $errorMessages)
                        @foreach ($errorMessages as $errorMessage)
                            <p>Row {{ $key }}: {{ $errorMessage }}</p>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endpush
@push('custom-scripts')
    <script type="text/javascript">
        window.addEventListener('load', function() {
            // Form Validation
            let validator = validateForm($('form[name="bulkImportOrganization"]'), {}, {})
            $('.save-btn').on('click', function(e) {
                e.preventDefault();
                const button = $(this);
                const loader = $('.import-loader');
                // button.prop('disabled', true);
                let validator = validateForm($('form[name="bulkImportOrganization"]'), {}, {})
                let url = $('form[name="bulkImportOrganization"]').attr('action')
                let method = $('form[name="bulkImportOrganization"]').attr('method')
                if (validator.form()) {
                    let formElement = $('form[name="bulkImportOrganization"]')[0];
                    let formData = new FormData(formElement);

                    loader.removeClass('d-none');
                    button.prop('disabled', true);
                    $.ajax({
                        url: url,
                        method: method,
                        data: formData,
                        processData: false, // Prevent jQuery from processing the data
                        contentType: false, // Prevent jQuery from setting content type
                        success: function(response) {
                            swalAlert2(response.status, response.message, response.redirect)
                            setTimeout(function() {
                                window.location.href = response.redirect
                            }, 3000);
                        },
                        error: function(response) {
                            const status = response.responseJSON?.status || 'error';
                            const errors = response.responseJSON?.errors || 'Something went wrong during import.';
                            swalAlert(status, errors, 7000)
                            button.prop('disabled', false);
                        },
                        complete: function() {
                            button.prop('disabled', false);
                            loader.addClass('d-none');
                        }
                    });
                } else {
                    button.prop('disabled', false);
                }
            })
        })
    </script>
@endpush
