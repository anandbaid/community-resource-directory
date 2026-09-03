@extends('backend.layouts.app')
@section('title', ' | Categories')
@section('content')

    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Service Categories</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Service Categories
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
                            <h5 class="card-title mb-0">Categories</h5>
                            <a class="btn btn-primary float-sm-end" href="{{ route('admin.category.create') }}">Add New</a>
                            <br class="float-none">
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-striped data-table">
                                <thead>
                                    <tr>
                                        <th scope="col">Category Name</th>
                                        <th scope="col">Category Order</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($categories) > 0)
                                        @foreach ($categories as $category)
                                            <tr data-id="">
                                                <td>{{ $category->name }}</td>
                                                <td>{{ $category->category_order }}</td>
                                                <td>{{ $category->status }}</td>
                                                <td>
                                                    <a href="{{ route('admin.category.edit', $category->id) }}"
                                                        class="btn btn-primary">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </a>
                                                    <button type="button" class="btn btn btn-danger delete-category"
                                                        data-id="{{ $category->id }}" data-type="delete"
                                                        data-url="{{ route('admin.category.destroy', $category->id) }}">
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
                            @if (count($categories) > 0)
                                {{ $categories->links() }}
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
            $('.delete-category').on('click', function() {
                let element = $(this);
                let url = $(this).attr('data-url');
                Swal.fire({
                    title: 'Are you sure, you want to delete the category?',
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
