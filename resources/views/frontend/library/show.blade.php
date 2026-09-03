@extends('frontend.layouts.app')
@section('title', ' | Library Details')
@section('light-back', 'light-back')

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
                                    <h2 class="bannerHead">Library Details</h2>
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

        <section>
            <div class="bg-container container">
                <div class="panel">
                    <div class="white-box" data-aos="fade-up">
                        <div class="mb-4">
                            <a href="{{ route('library', $filters ?? []) }}" class="blueLink">&larr; Back to Library</a>
                        </div>
                        <div class="row gx-5 gy-4">
                            <div class="col-md-4">
                                <img src="{{ isset($publication->image) ? asset($publication->image) : asset('assets/img/image-here.png') }}"
                                    alt="{{ $publication->title }}">
                            </div>
                            <div class="col-md-8 lib-cont">
                                <h4 class="mb-2">{{ $publication->title }}</h4>
                                @if ($stateName)
                                    <div class="mb-2"><strong>Location:</strong> {{ $stateName }}</div>
                                @endif
                                @php
                                    $organizations = $publication->organizations ?? collect();
                                @endphp
                                @if ($organizations->isNotEmpty())
                                    <div class="mb-2"><strong>Organizations:</strong>
                                        @foreach ($organizations as $org)
                                            <a href="{{ url('organization-details/' . $org->id) }}" class="textLink">
                                                {{ $org->name }}</a>{{ $loop->last ? '' : ', ' }}
                                        @endforeach
                                    </div>
                                @endif
                                <p class="mb-3">{!! $publication->description !!}</p>
                                @if ($publication->url)
                                    <div class="mb-3"><span class="org-bd">Link:</span>
                                        <a href="{{ url($publication->url) }}" target="_blank" class="textLink">
                                            {{ $publication->url }}
                                        </a>
                                    </div>
                                @endif
                                @php
                                    $shareUrl = route(
                                        'library.show',
                                        array_merge(['id' => $publication->id], $filters ?? []),
                                    );
                                @endphp
                                <div class="ico-box mt-3">
                                    @if ($publication->file)
                                        <a href="{{ url('download-resource/' . $publication->id) }}" class="ico"
                                            title="Download"><i class="fa-solid fa-download"></i></a>
                                        <a href="#" class="ico"
                                            onclick="printFile(event, '{{ asset($publication->file) }}')" title="Print"><i
                                                class="fa-solid fa-print"></i></a>
                                    @endif
                                    @if (!empty($shareLinks))
                                        <a href="#" class="ico share-trigger"
                                            data-url="{{ route('library.show', ['id' => $publication->id]) }}"
                                            data-title="{{ $publication->title }}"
                                            data-facebook="{{ $shareLinks['facebook'] ?? '' }}"
                                            data-twitter="{{ $shareLinks['twitter'] ?? '' }}"
                                            data-linkedin="{{ $shareLinks['linkedin'] ?? '' }}"
                                            data-whatsapp="{{ $shareLinks['whatsapp'] ?? '' }}" title="Share">
                                            <i class="fa-solid fa-share-nodes"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@include('frontend.includes.share-modal')
