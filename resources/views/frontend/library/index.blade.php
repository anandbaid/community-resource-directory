@extends('frontend.layouts.app')
@section('title', ' | Library')
@section('light-back', 'light-back')
@push('custom-styles')
    <style>
        .library-select {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: flex-end;
        }

        .library-select .reset-filter-btn {
            padding: 7px 15px;
        }

        .library-list-wrapper {
            min-height: 480px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .empty-state-box {
            min-height: 260px;
            background: #f7f7f7;
            border: 1px solid #e5e5e5;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 32px 24px;
        }

        .lib-desc {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .sort-list .national-option {
            color: #E92429;
            text-decoration: underline;
            font-weight: 600;
        }

        .library-grid .library-box {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .library-grid .library-box .row {
            flex: 1;
        }

        .library-grid .lib-cont {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .textLink {
            text-wrap: wrap;
            word-wrap: anywhere;
            white-space: wrap;
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
                        <div class="swiper-slide bannerBack innerBanner"
                            data-bg-small="{{ $smallImage }}"
                            data-bg-medium="{{ $mediumImage }}"
                            data-bg-original="{{ asset($banner->image) }}"
                            style="background-image: url('{{ $bannerImage }}');">
                            <div class="container banContainer">
                                <div class="df-column bannerCont" data-aos="fade-up">
                                    <h2 class="bannerHead">Library</h2>
                                    <div class="bookmark"><a href="{{ url('/') }}">Home</a>Library
                                    </div>
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
            <div class="bg-container container">
                <div class="panel">
                    <h2 data-aos="fade-up" class="text-center">
                        <span class="lt">Search </span>
                            Library
                        </h2>
                    <div data-aos="fade-up" class="text-center mb-md-5 mb-3">
                        {!! $libraryContent ?? '' !!}
                    </div>
                    @php
                        $libraryFilterProps = [
                            'baseUrl' => url('library'),
                            'states' => $states->map(fn ($state) => ['id' => (string) $state->id, 'name' => $state->name])->values(),
                            'selectedState' => (string) ($queryParam['state'] ?? ''),
                            'order' => $queryParam['order'] ?? 'asc',
                        ];
                    @endphp
                    <div class="library-head" data-aos="fade-up">
                        <h3 class="admin-head"></h3>
                        <div data-vue-island="library-filters"
                            data-vue-props="{{ json_encode($libraryFilterProps + ['section' => 'sort']) }}"></div>
                    </div>
                    <div class="row gx-5 gy-4">
                        <div class="col-lg-3" data-aos="fade-up">
                            <div class="sort-loc-filter-box">
                                <div class="sort-filter">
                                    <div class="filter-ico"></div> Filter Location
                                </div>
                                <div class="sort-loc">
                                    <div class="sort-head">
                                        <h6>Sort by Geographic Location.</h6>
                                    </div>
                                    <div data-vue-island="library-filters"
                                        data-vue-props="{{ json_encode($libraryFilterProps + ['section' => 'locations']) }}"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-9">
                            <div class="library-list-wrapper">
                                <div class="row gy-4 library-grid">
                                    @if ($publications->count())
                                        @foreach ($publications as $publication)
                                            @php
                                                $shareUrl = route(
                                                    'library.show',
                                                    array_merge(['id' => $publication->id], $queryParam ?? []),
                                                );
                                                $shareLinks = Share::page($shareUrl, $publication->title)
                                                    ->facebook()
                                                    ->twitter()
                                                    ->linkedin()
                                                    ->whatsapp()
                                                    ->getRawLinks();
                                            @endphp
                                            <div class="col-xl-6 col-lg-12 d-flex" data-aos="fade-up">
                                                <div class="library-box">
                                                    <div class="row gy-4 h-100">
                                                        <div class="col-sm-4 df-column">
                                                            <a href="{{ $shareUrl }}"><img src="{{ isset($publication->image) ? asset($publication->image) : asset('assets/img/image-here.png') }}"
                                                                alt=""></a>
                                                            <div class="ico-box justify-content-center">
                                                                @if (isset($publication->file))
                                                                    <a href="{{ url('download-resource/' . $publication->id) }}"
                                                                        class="ico"><i
                                                                            class="fa-solid fa-download"></i></a>
                                                                    <a href="#"
                                                                        class="ico"
                                                                        onclick="printFile(event, '{{ asset($publication->file) }}')"><i
                                                                            class="fa-solid fa-print"></i></a>
                                                                @endif
                                                                <a href="#" class="ico share-trigger"
                                                                    data-url="{{ route('library.show', ['id' => $publication->id]) }}"
                                                                    data-title="{{ $publication->title }}"
                                                                    data-facebook="{{ $shareLinks['facebook'] ?? '' }}"
                                                                    data-twitter="{{ $shareLinks['twitter'] ?? '' }}"
                                                                    data-linkedin="{{ $shareLinks['linkedin'] ?? '' }}"
                                                                    data-whatsapp="{{ $shareLinks['whatsapp'] ?? '' }}"
                                                                    title="Share">
                                                                    <i class="fa-solid fa-share-nodes"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-8 lib-cont">
                                                            <a href="{{ $shareUrl }}" class="text-decoration-none"><h6>{{ $publication->title }}</h6></a>
                                                            <p class="lib-desc">{!! strip_tags($publication->description) !!}</p>
                                                            @php
                                                                $organizationNames = $publication->organizations
                                                                    ? $publication->organizations->pluck('name')->toArray()
                                                                    : [];
                                                            @endphp
                                                            @if (count($organizationNames) > 0)
                                                                <div class="one-line-ellip"><span class="org-bd">Organizations:
                                                                    </span>{{ implode(', ', $organizationNames) }}</div>
                                                            @endif
                                                            <a href="{{ $shareUrl }}" class="redLink">View Details</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="col-12">
                                            <div class="empty-state-box">
                                                <h6>No records found</h6>
                                                <p>Try selecting a different location or reset the filters.</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <!-- pagination start -->
                            <div class="cust-pagination pagination" data-aos="fade-up">
                                @if (!$publications->isEmpty())
                                    {{ $publications->appends($queryParam)->links() }}
                                @endif
                                {{-- <a href="#" class="page-prev"><i class="fa-solid fa-angle-left"></i></a>
                                <a class="page-numbers" href="#">1</a>
                                <a class="page-numbers current" href="#">2</a>
                                <a class="page-numbers" href="#">3</a>
                                <span class="page-numbers dots">…</span>
                                <a class="page-numbers" href="#">4</a>
                                <a href="#" class="page-next"><i class="fa-solid fa-angle-right"></i></a> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@include('frontend.includes.share-modal')
