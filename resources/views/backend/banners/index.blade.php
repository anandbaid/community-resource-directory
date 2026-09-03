@extends('backend.layouts.app')
@section('title', ' | Page Banners')
@section('content')

    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Page Banners</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Page Banners
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
                            <h5 class="card-title mb-0">Page Banners</h5>
                            <a class="btn btn-primary float-end"
                                href="{{ route('admin.banner.create') }}">{{ __('Add New') }}</a>
                            <br class="float-none">

                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-striped data-table">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ __('Page Name') }}</th>
                                        <th scope="col">{{ __('Image') }}</th>
                                        <th scope="col">{{ __('Status') }}</th>
                                        <th scope="col">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!$banners->isEmpty())
                                        @foreach ($banners as $banner)
                                            <tr>
                                                <td>{{ $banner->page_title ?? '' }}</td>
                                                <td>
                                                    <img src="{{ $banner->image == null ? asset('assets/img/placeholder.png') : asset($banner->image) }}"
                                                        alt="Image" width="100" height="60" class="">
                                                </td>
                                                <td>{{ ucfirst($banner->status) ?? '' }}</td>
                                                <td>
                                                    <a href="{{ route('admin.banner.edit', $banner->id) }}"
                                                        class="btn btn-primary"><i class="fa-solid fa-pen"></i></a>
                                                    <button type="button" class="btn btn btn-danger delete-banner"
                                                        data-id="{{ $banner->id }}" data-type="delete"
                                                        data-url="{{ route('admin.banner.destroy', $banner->id) }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center">No record found.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            @if (count($banners) > 0)
                                {{ $banners->links() }}
                            @endif
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
            $('.delete-banner').on('click', function() {
                let element = $(this);
                let url = $(this).attr('data-url');
                Swal.fire({
                    title: 'Are you sure, you want to delete the Banner?',
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
