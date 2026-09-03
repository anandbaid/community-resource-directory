@extends('frontend.layouts.app')
@section('title', ' | Saved Search Results')
@section('light-back', 'light-back')
@section('content')
    <main>
        <!-- search result start -->
        <section>
            <div class="container panel bg-container panel-bottom">
                <div class="row gy-4 gx-4">
                    <div class="col-lg-3" data-aos="fade-up">
                        @include('frontend.includes.quick-links')
                    </div>
                    <div class="col-lg-9">
                        <div class="text-center mb-50">
                            <h3 data-aos="fade-up" class="text-center admin-head">
                                Saved Search Results
                            </h3>
                        </div>
                        <div class="white-box resource-frm-container" data-aos="fade-up" data-aos-delay="100">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Search Results</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($savedSearchs))
                                        @foreach ($savedSearchs as $resultKey => $savedSearch)
                                            @php
                                                $search = json_decode($savedSearch->search_params, true);
                                                $string = '';
                                                $searchParams = '';
                                                foreach ($search as $key => $item) {
                                                    if (!is_null($item)) {
                                                        $string .= $key . '=' . $item . '&';
                                                        if ($key != 'advance') {
                                                            if ($key == 'category') {
                                                                $searchParams .=
                                                                    str_replace('_', ' ', ucfirst($key)) .
                                                                    '=' .
                                                                    $getCategories($item)->name .
                                                                    ', ';
                                                            } else {
                                                                $searchParams .=
                                                                    str_replace('_', ' ', ucfirst($key)) .
                                                                    '=' .
                                                                    $item .
                                                                    ', ';
                                                            }
                                                        }
                                                    }
                                                }
                                                $string = trim($string, '&');
                                                $searchParams = trim($searchParams, ', ');
                                            @endphp
                                            <tr data-aos="fade-up" data-aos-delay="{{ 100 + ($resultKey * 50) }}">
                                                <td>{{ $resultKey + 1 }}</td>
                                                <td><a href="{{ url('search-resources') . '?' . $string }}">Search - Based
                                                        on
                                                        {{ $searchParams }}</a></td>
                                                <td><a href="{{ url('/download-search/' . $savedSearch->id) }}"
                                                        class="download-search"><i class="fa-solid fa-download"></i></a>
                                                    <a href="javascript:void(0);" class="delete-search"
                                                        onclick="deleteData($(this), true)"
                                                        data-url="{{ url('delete-search', $savedSearch->id) }}"
                                                        data-message="to delete the saved search list"
                                                        data-id="{{ $savedSearch->id }}"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="3" class="text-center">No records found</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@push('custom-scripts')
    <script type="text/javascript">
        // window.addEventListener('load', function() {
        //     $(document).on('click', '.delete-search', function(e) {
        //         e.preventDefault();
        //         let searchId = $(this).attr('data-id'),
        //             url = "{{ url('delete-search') }}",
        //             method = 'post';
        //         $.ajax({
        //             url: url,
        //             method: method,
        //             data: {
        //                 searchId: searchId
        //             },
        //             success: function(response) {
        //                 swalAlert2(response.status, response.message, response.redirect)
        //                 setTimeout(function() {
        //                     window.location.href = response.redirect
        //                 }, 1500);
        //             },
        //             error: function(response) {
        //                 swalAlert(response.responseJSON.status, response.responseJSON
        //                     .errors, 7000)
        //             }
        //         });
        //     })
        // })
    </script>
@endpush
