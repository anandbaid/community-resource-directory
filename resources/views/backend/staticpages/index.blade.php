@extends('backend.layouts.app')
@section('title', ' | Static Pages')
@section('content')

    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Static Pages</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Static Pages
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
                            <h5 class="card-title mb-0">Static Pages</h5>
                            <a class="btn btn-primary float-end" href="{{ route('admin.static-pages.create') }}">Add New</a>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-striped data-table">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ __('Page Name') }}</th>
                                        <th scope="col">{{ __('Slug') }}</th>
                                        <th scope="col">{{ __('Header') }}</th>
                                        <th scope="col">{{ __('Footer') }}</th>
                                        <th scope="col">{{ __('Status') }}</th>
                                        <th scope="col">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!$staticPages->isEmpty())
                                        @foreach ($staticPages as $staticPage)
                                            <tr>
                                                <td>{{ $staticPage->title ?? '' }}</td>
                                                <td>{{ $staticPage->slug ?? '' }}</td>
                                                <td>
                                                    {{ $staticPage->show_in_header ? 'Yes' : 'No' }}
                                                    @if ($staticPage->show_in_header && $staticPage->header_parent)
                                                        ({{ $staticPage->header_parent }})
                                                    @endif
                                                </td>
                                                <td>{{ $staticPage->show_in_footer ? 'Yes' : 'No' }}</td>
                                                <td>{{ ucfirst($staticPage->status) ?? '' }}</td>
                                                <td>
                                                    <a href="{{ route('admin.static-pages.edit', $staticPage->id) }}"
                                                        class="btn btn-primary"><i class="fa-solid fa-pen"></i></a>
                                                    @php
                                                        $isLegacyPage = in_array($staticPage->slug, ($GLOBALS['LEGACY_STATIC_PAGE_SLUGS'] ?? []), true);
                                                    @endphp
                                                    @if (!$isLegacyPage)
                                                        <button type="button"
                                                            class="btn btn-danger delete-static-page"
                                                            data-url="{{ route('admin.static-pages.destroy', $staticPage->id) }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center">No record found.</td>
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
            $('.delete-static-page').on('click', function() {
                let url = $(this).attr('data-url');
                Swal.fire({
                    title: 'Are you sure, you want to delete this page?',
                    text: 'This action could not be reverted',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
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
                            error: function(xhr) {
                                swalAlert(xhr.responseJSON.status, xhr.responseJSON.errors, 4000)
                            }
                        })
                    }
                });
            });
        });
    </script>
@endpush
