@extends('frontend.layouts.app')
@section('title', ' | Organization Details')
@push('custom-styles')
    <style>
        .load-more-publication {
            background-color: transparent;
            padding-top: 7px;
            padding-bottom: 7px;
        }

        .org-address {
            display: flex;
            align-items: flex-start;
            gap: 5px;
        }

        .org-address .org-bd {
            min-width: 140px;
            display: inline-block;
        }
    </style>
@endpush
@section('content')
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
                            data-bg-medium="{{ $mediumImage }}" data-bg-original="{{ asset($banner->image) }}"
                            style="background-image: url('{{ $bannerImage }}');">
                            <div class="container banContainer">
                                <div class="df-column bannerCont" data-aos="fade-up">
                                    <h2 class="bannerHead">Organization details</h2>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if (count($banners) > 1)
                    <div class="homeBanNext"><i class="fa-solid fa-angle-right"></i></div>
                    <div class="homeBanPrev"><i class="fa-solid fa-angle-left"></i></div>
                @endif
            </div>
        </section>

        <!-- organization content start -->
        <section>
            <div class="container">
                <div class="panel">
                    <div class="white-box" data-aos="fade-up">
                        @if ($user)
                            <div class="ico-box org-wish-box">
                                @if ($user && $user->role != 'admin')
                                    @if ($ratingExists)
                                        <span class="ico sm-ico active"><i class="fa-regular fa-thumbs-down"></i></span>
                                    @else
                                        <span data-vue-island="report-spam-modal" data-vue-props="{{ json_encode([
                                            'orgId' => $organization->id,
                                            'submitUrl' => route('report-spam'),
                                        ]) }}"></span>
                                        <a href="{{ url('review-rating/' . $organization->id) }}" class="ico sm-ico"><i
                                                class="fa-regular fa-thumbs-down"></i></a>
                                    @endif
                                    <span data-vue-island="save-resource-toggle" data-vue-props="{{ json_encode([
                                        'url' => url('saved-resource/' . $organization->id),
                                        'saved' => (bool) $resourceExists,
                                    ]) }}"></span>
                                @endif
                            </div>
                        @endif
                        <div class="row gx-5 gy-4 mb-5">
                            <div class="col-md-4" data-aos="fade-right">
                                <div class="swiper-container org-swiper">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <img src="{{ asset($organization->logo) }}" alt="">
                                        </div>
                                        {{-- <div class="swiper-slide">
                                            <img src="assets/img/image-here.png" alt="">
                                        </div> --}}
                                    </div>
                                    {{-- <div class="org-pagination"></div> --}}
                                </div>
                            </div>
                            <div class="col-md-8 org-cont" data-aos="fade-left">
                                <h6>
                                    {{ $organization->name }}
                                </h6>
                                <div class="org-location-box">
                                    <span><i class="fa-solid fa-location-dot"></i> <span class="org-loc">
                                            {{ $organizationDetails->physical_state }}</span></span>
                                    <span><span class="org-rating">
                                            {!! $ratingDetails['starHtml'] !!}
                                        </span>({{ $ratingDetails['count'] }}
                                        Ratings)</span>
                                </div>
                                <div><span class="org-bd">Organization Type: </span>
                                    {{ $organization->type == 'government' ? 'Government Organization' : 'Non-Government Organization' }}
                                </div>
                                <div><span class="org-bd">Service Categories: </span>{{ implode(', ', $categories) }}</div>
                                <div><span class="org-bd">Description:
                                    </span>{{ $organizationDetails->service_description }}</div>
                                <div class="org-address"><span class="org-bd">Physical Address:
                                    </span>
                                    <div>
                                        <div>
                                            {{ $organizationDetails->physical_address_1 }}
                                        </div>
                                        @if (!empty($organizationDetails->physical_address_2))
                                            <div>
                                                {{ $organizationDetails->physical_address_2 }}
                                            </div>
                                        @endif
                                        <div>
                                            {{ $organizationDetails->physical_city }},
                                            {{ $organizationDetails->physical_state }},
                                            {{ $organizationDetails->physical_postal_code }}
                                        </div>
                                    </div>
                                </div>
                                <div class="org-address"><span class="org-bd">Mailing Address:</span>
                                    <div>
                                        @if (!empty($organizationDetails->mailing_address_1))
                                            <div>
                                                {{ $organizationDetails->mailing_address_1 }}
                                            </div>
                                        @endif
                                        @if (!empty($organizationDetails->mailing_address_2))
                                            <div>
                                                {{ $organizationDetails->mailing_address_2 }}
                                            </div>
                                        @endif
                                        <div>
                                            {{ !empty($organizationDetails->mailing_city) ? $organizationDetails->mailing_city : '' }}{{ !empty($organizationDetails->mailing_state) ? ', ' . $organizationDetails->mailing_state : '' }}{{ !empty($organizationDetails->mailing_postal_code) ? ', ' . $organizationDetails->mailing_postal_code : '' }}
                                        </div>
                                    </div>
                                </div>
                                @if ($organizationDetails->file_url)
                                    <div><span class="org-bd">File URL: </span>{{ $organizationDetails->file_url }}</div>
                                @endif
                                <div><span class="org-bd">Website: </span>{{ $organization->website }}</div>
                                <div><span class="org-bd">Email: </span>{{ $organization->email }}</div>
                                <div><span class="org-bd">Phone Number:
                                    </span>{{ \App\Http\Controllers\CommonFunction::formatPhone($organization->phone) }}
                                </div>
                                <div class="ico-box">
                                    @php
                                        // social_links is nullable, and json_decode returns null for
                                        // both an absent value and malformed JSON. Indexing that
                                        // directly 500s the page for any organization saved without
                                        // social links.
                                        $socialLinks = json_decode($organizationDetails->social_links ?? '', true);
                                        $socialLinks = is_array($socialLinks) ? $socialLinks : [];
                                    @endphp
                                    @if (!empty($socialLinks['facebook']))
                                        <a href="{{ $socialLinks['facebook'] }}" target="_blank" class="ico big-ico"><i
                                                class="fa-brands fa-facebook-f"></i></a>
                                    @endif
                                    @if (!empty($socialLinks['linkedin']))
                                        <a href="{{ $socialLinks['linkedin'] }}" target="_blank" class="ico big-ico"><i
                                                class="fa-brands fa-linkedin-in"></i></a>
                                    @endif
                                    @if (!empty($socialLinks['instagram']))
                                        <a href="{{ $socialLinks['instagram'] }}" target="_blank" class="ico big-ico"><i
                                                class="fa-brands fa-instagram"></i></a>
                                    @endif
                                </div>
                                <div><span class="org-bd">Publications: </span></div>
                                <div data-vue-island="publication-grid" data-vue-props="{{ json_encode([
                                    'publications' => $publications,
                                    'total' => $publicationTotal,
                                    'loadMoreUrl' => url('/get-more-publication/' . $organization->id),
                                ]) }}"></div>
                            </div>
                        </div>
                        <div class="org-map">
                            <div data-vue-island="resource-map" data-vue-props="{{ json_encode([
                                'apiKey' => config('custom.map_api_key'),
                                'locations' => $location_array,
                                'detailsUrl' => url('/organization-details'),
                            ]) }}"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
@include('frontend.includes.share-modal')
@push('custom-scripts')
    <script>
        @if (Session::has('message'))
            swalAlert("success", "{{ session('message') }}", 7000);
        @endif
    </script>
@endpush
