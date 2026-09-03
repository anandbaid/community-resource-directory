@extends('frontend.layouts.app')
@section('title', ' | Saved Resources')
@section('light-back', 'light-back')
@push('custom-styles')
    <style>
        .saved-resource {
            cursor: pointer;
        }

        .saved-card {
            position: relative;
        }

        .saved-card-loader {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        .saved-card-loader.d-none {
            display: none;
        }

        .saved-card-loader .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid #dcdcdc;
            border-top-color: #c53030;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush
@section('content')
    <main>
        <!-- search result start -->
        <section>
            <div class="container panel bg-container panel-bottom">
                <div class="row gy-4 gx-4">
                    <div class="col-lg-3">
                        @include('frontend.includes.quick-links')
                    </div>
                    <div class="col-lg-9">
                        <div class="text-center mb-50">
                            <h3 data-aos="fade-up" class="text-center admin-head">
                                Saved Resources
                            </h3>
                        </div>
                        <div class="row gx-4 gy-4 saved-resources-grid">
                            @forelse ($organizations as $organization)
                                <div class="col-lg-6" data-aos="fade-up">
                                    <div class="search-box saved-card">
                                        <div class="saved-card-loader d-none">
                                            <div class="spinner"></div>
                                        </div>
                                        @php
                                            $resourceRatings = $getStarDetails($organization->id);
                                            $categories = $getCategories($organization->category);
                                            $publicationNames = $getPublications($organization->id);
                                            $ratingDetails = $getRatings($organization->id);
                                        @endphp
                                        <div class="ico-box search-wish-box">
                                            @if ($resourceRatings['ratingExists'])
                                                <span class="ico sm-ico active"><i class="fa-regular fa-thumbs-down"></i></span>
                                            @else
                                                <a href="{{ url('review-rating/' . $organization->id) }}" class="ico sm-ico"><i
                                                        class="fa-regular fa-thumbs-down"></i></a>
                                            @endif
                                            <span data-url="{{ url('saved-resource/' . $organization->id) }}"
                                                data-status="{{ $resourceRatings['resourceExists'] == 1 ? 'exists' : 'not-exists' }}"
                                                class="ico sm-ico saved-resource {{ $resourceRatings['resourceExists'] == 1 ? 'active' : '' }}"><i
                                                    class="fa-regular fa-heart"></i></span>
                                        </div>
                                        <h6>{{ $organization->name }}</h6>
                                        <div class="org-location-box">
                                            <span><i class="fa-solid fa-location-dot"></i> <span class="org-loc">
                                                    {{ $organization->organizationDetails()->physical_state }}</span></span>
                                            <span>
                                                <span class="org-rating">
                                                    {!! $ratingDetails['starHtml'] !!}
                                                </span>
                                                ({{ $ratingDetails['count'] }} Ratings)
                                            </span>
                                        </div>
                                        <div>
                                            <span class="org-bd">Organization Type:
                                            </span>{{ $organization->type == 'government' ? 'Government Organization' : 'Non-Government Organization' }}
                                        </div>
                                        <div class="one-line-ellip"><span class="org-bd">Service Categories:
                                            </span>{{ implode(', ', $categories) }}</div>
                                        <div class="two-line-ellip"><span class="org-bd">Description:
                                            </span>{{ $organization->organizationDetails()->description }}</div>
                                        <div class="one-line-ellip"><span class="org-bd">Publications:
                                            </span>{{ implode(', ', $publicationNames) }}</div>
                                        <a href="{{ url('/organization-details/' . $organization->id) }}" class="redLink">View
                                            Details</a>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center no-saved-message">No saved resources.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@push('custom-scripts')
    <script type="text/javascript">
        window.addEventListener('load', function() {
            $(document).on('click', '.saved-resource', function(e) {
                e.preventDefault();
                const ele = $(this);
                const url = ele.data('url');
                const saved = ele.data('status');
                const card = ele.closest('.saved-card');
                const loader = card.find('.saved-card-loader');

                loader.removeClass('d-none');
                $.ajax({
                    url: url,
                    method: 'get',
                    data: {
                        saved: saved,
                    },
                    success: function() {
                        const newStatus = saved === 'exists' ? 'not-exists' : 'exists';
                        ele.toggleClass('active').attr('data-status', newStatus);

                        if (saved === 'exists') {
                            card.closest('.col-lg-6').fadeOut(200, function() {
                                $(this).remove();
                                const grid = $('.saved-resources-grid');
                                if (!grid.find('.col-lg-6').length && !grid.find('.no-saved-message').length) {
                                    grid.append(
                                        '<div class="col-12 text-center no-saved-message">No saved resources.</div>');
                                }
                            });
                        }
                        loader.addClass('d-none');
                    },
                    error: function() {
                        console.log('error occurred while updating saved resource');
                        loader.addClass('d-none');
                    }
                });
            });
        })
    </script>
@endpush
