@extends('backend.layouts.app')
@section('title', ' | Reviews')
@push('custom-styles')
    <style>
        .bi-star-fill {
            color: #ffd018;
        }
    </style>
@endpush
@section('content')

    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Organization Reviews</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Organization Reviews
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
                            <h5 class="card-title mb-0">Reviews</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-striped data-table">
                                <thead>
                                    <tr>
                                        <th scope="col">User</th>
                                        <th scope="col">Organization</th>
                                        <th scope="col">Rating</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($reviews) > 0)
                                        @foreach ($reviews as $review)
                                            <tr data-id="">
                                                <td>{{ $review->userDetails->name }}</td>
                                                <td>{{ $review->organizationDetails->name }}</td>
                                                <td>
                                                    @for ($i = 1; $i <= $review->rate; $i++)
                                                        <i class="bi bi-star-fill"></i>
                                                    @endfor
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.review.show', $review->id) }}"
                                                        class="btn btn-primary">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
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
                            @if (count($reviews) > 0)
                                {{ $reviews->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
