@extends('frontend.layouts.app')
@section('title', ' | Search Organization')
@section('light-back', 'light-back')
@push('custom-styles')
    <style>
        #map {
            height: 90vh;
            width: 100%;
        }

        .controls {
            padding: 10px;
            background: #f8f8f8;
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }

        input,
        button {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .member_tag {
            position: relative;
            top: 0;
            right: 0;
            width: auto;
            display: flex;
            align-items: center;
        }

        .member_icon {
            width: 115px;
            height: auto;
            display: block;
        }

        .search-wish-box {
            gap: 12px;
        }

        .search-wish-box.member-exists {
            right: 0;
        }

        .search-sort {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-sort select {
            width: 110px;
            padding: 15px 34px 15px 14px !important;
            font-size: var(--font-16);
            background-color: #ffffff;
            border: none;
            box-shadow: 2px 3px 6px rgba(0, 0, 0, 0.1);
        }

        .search-result-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
    </style>
@endpush
@section('content')
    {{--
        The result cards and pagination stay server rendered: this listing is the
        crawl path to every /organization-details/{id} page. Only the interactive
        pieces are Vue islands (see resources/js/site.js).
    --}}
    <main>
        <!-- bannner start -->
        <section>
            <div class="swiper-container home-swiper">
                <div class="swiper-wrapper">
                    @foreach ($banners ?? [] as $banner)
                        @php
                            $relativePath = ltrim(str_replace('/storage/', '', $banner->image), '/');
                            $smallPath = dirname($relativePath) . '/small/' . basename($relativePath);
                            $mediumPath = dirname($relativePath) . '/medium/' . basename($relativePath);
                            $disk = \Illuminate\Support\Facades\Storage::disk('public');
                            $smallImage = $disk->exists($smallPath) ? asset('storage/' . $smallPath) : '';
                            $mediumImage = $disk->exists($mediumPath) ? asset('storage/' . $mediumPath) : '';
                            $bannerImage = $mediumImage ?: ($smallImage ?: asset($banner->image));
                        @endphp
                        <div class="swiper-slide bannerBack innerBanner" data-bg-small="{{ $smallImage }}"
                            data-bg-medium="{{ $mediumImage }}"
                            data-bg-original="{{ asset($banner->image) }}"
                            style="background-image: url('{{ $bannerImage }}');">
                            <div class="container banContainer">
                                <div class="df-column bannerCont" data-aos="fade-up">
                                    <h2 class="bannerHead">Resources</h2>
                                    <div class="bookmark"><a href="{{ url('/') }}">Home</a>Resources
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if (count($banners ?? []) > 1)
                    <div class="homeBanNext"><i class="fa-solid fa-angle-right"></i></div>
                    <div class="homeBanPrev"><i class="fa-solid fa-angle-left"></i></div>
                @endif
            </div>
        </section>
        <!-- search result start -->
        <section>
            <div class="container panel-top">
                <div class="text-center">
                    <h2 data-aos="fade-up" class="text-center">
                        <span class="lt">Search </span>Resources
                    </h2>
                </div>
                <div data-aos="fade-up" class="text-center mb-md-5 mb-3">
                    {!! $resources ?? '' !!}
                </div>
                <!-- find resource start -->
                <div data-vue-island="resource-search-form" data-vue-props="{{ json_encode([
                    'action' => url('search-resources'),
                    'suggestUrl' => url('suggest-new-resources'),
                    'states' => $states->pluck('name')->values(),
                    'categories' => $categories->map(fn($category) => ['id' => $category->id, 'name' => $category->name])->values(),
                    'query' => (object) $queryParam,
                ]) }}"></div>
            </div>
        </section>
        <!-- mapping start -->
        <section class="custMap">
            <div data-vue-island="resource-map" data-vue-props="{{ json_encode([
                'apiKey' => config('custom.map_api_key'),
                'locations' => $location_array,
                'detailsUrl' => url('/organization-details'),
            ]) }}"></div>
        </section>
        <section>
            <div class="container bg-container panel-bottom">
                <div class="search-result-head" data-aos="fade-up">
                    <h4>Search Results</h4>
                    <div data-vue-island="search-result-actions" data-vue-props="{{ json_encode([
                        'sort' => $sort ?? 'az',
                        'canSave' => (bool) ($authUser && $authUser->role != 'admin'),
                        'saveUrl' => url('save-search'),
                        'searchParams' => (object) $queryParam,
                        'resultIds' => array_values($resultedOrganizations),
                    ]) }}"></div>
                </div>
                <div class="row gx-4 gy-4 mt-0">
                    @if (count($organizations) > 0)
                        @foreach ($organizations as $organization)
                            <div class="col-lg-6" data-aos="fade-up">
                                <div class="search-box">
                                    @php
                                        $resourceRatings = $getStarDetails($organization->id);
                                        $categoryNames = $getCategories($organization->category);
                                        $publicationNames = $getPublications($organization->id);
                                        $ratingDetails = $getRatings($organization->id);
                                    @endphp
                                    <div
                                        class="ico-box search-wish-box {{ $organization->is_member == '1' ? 'member-exists' : '' }}">
                                        @if ($authUser && $authUser->role != 'admin')
                                            @if ($resourceRatings['ratingExists'])
                                                <span class="ico sm-ico active"><i
                                                        class="fa-regular fa-thumbs-down"></i></span>
                                            @else
                                                <a href="{{ url('review-rating/' . $organization->id) }}"
                                                    class="ico sm-ico"><i class="fa-regular fa-thumbs-down"></i></a>
                                            @endif
                                            <span data-vue-island="save-resource-toggle" data-vue-props="{{ json_encode([
                                                'url' => url('saved-resource/' . $organization->id),
                                                'saved' => (bool) $resourceRatings['resourceExists'],
                                            ]) }}"></span>
                                        @endif
                                        @if ($organization->is_member == '1')
                                            <span class="member_tag">
                                                <img src="{{ asset('/assets/img/memberTag.png') }}" class="member_icon">
                                            </span>
                                        @endif
                                    </div>
                                    <h6>{{ $organization->name }}</h6>
                                    <div class="org-location-box">
                                        <span><i class="fa-solid fa-location-dot"></i> <span class="org-loc">
                                                {{ $organization->physical_state }}</span></span>
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
                                        </span>{{ implode(', ', $categoryNames) }}
                                    </div>
                                    <div class="two-line-ellip"><span class="org-bd">Description:
                                        </span>{{ $organization->details->service_description ?? '' }}</div>
                                    <div class="one-line-ellip"><span class="org-bd">Publications:
                                        </span>{{ implode(', ', $publicationNames) }}</div>
                                    <a href="{{ url('/organization-details/' . $organization->id) }}"
                                        class="redLink">View
                                        Details</a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        @if (count($queryParam) != 0)
                            <div class="text-center">No Resources Found.</div>
                        @endif
                    @endif
                </div>
                <div class="cust-pagination pagination" data-aos="fade-up">
                    @if (count($organizations) > 0)
                        {{ $organizations->appends($queryParam)->links() }}
                    @endif
                </div>
            </div>
        </section>
    </main>
@endsection
