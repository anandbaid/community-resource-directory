@extends('frontend.layouts.app')
@section('title', ' | Dashboard')
@section('light-back', 'light-back')
@section('content')
    <main>
        <!-- banner start -->
        <section>
            <div class="bannerBack innerBanner" style="background-image: url({{ asset('assets/img/banner.png') }});">
                <div class="container banContainer">
                    <div class="df-column bannerCont" data-aos="fade-up">
                        <h2 class="bannerHead">Dashboard</h2>
                        <p class="banPara">Welcome back, {{ $user->first_name ?? $user->name }}.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- dashboard content -->
        <section>
            <div class="container panel bg-container panel-bottom">
                <div class="row gy-4 gx-4">
                    <div class="col-lg-3" data-aos="fade-up">
                        @include('frontend.includes.quick-links')
                    </div>
                    <div class="col-lg-9">
                        <div class="row gy-4">
                            <div class="col-md-4" data-aos="fade-up">
                                <div class="card shadow-sm h-100 border-0">
                                    <div class="card-body">
                                        <p class="text-uppercase text-muted small mb-2">Profile</p>
                                        <h5 class="mb-1">{{ $user->name }}</h5>
                                        <p class="mb-2 text-muted sz-16">{{ $user->email }}</p>
                                        <p class="mb-0 text-muted sz-16">
                                            {{ $user->phone ? \App\Http\Controllers\CommonFunction::formatPhone($user->phone) : 'Phone not added yet' }}
                                        </p>
                                        <a href="{{ route('user.profile') }}"
                                            class="textLink text-decoration-none mt-3 d-inline-flex align-items-center gap-2">View
                                            Profile <i class="fa-solid fa-arrow-right-long"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                                <div class="card shadow-sm h-100 border-0">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="text-uppercase text-muted small">Saved Resources</span>
                                            <a href="{{ url('saved-resources-view') }}" class="textLink">View all</a>
                                        </div>
                                        <h3 class="mb-3">{{ $savedResourcesCount }}</h3>
                                        <p class="text-muted mb-0">Keep your favorite organizations together.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                                <div class="card shadow-sm h-100 border-0">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="text-uppercase text-muted small">Saved Searches</span>
                                            <a href="{{ url('saved-search-view') }}" class="textLink">View all</a>
                                        </div>
                                        <h3 class="mb-3">{{ $savedSearchCount }}</h3>
                                        <p class="text-muted mb-0">Reuse your most common searches quickly.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card shadow-sm border-0 mt-4" data-aos="fade-up" data-aos-delay="150">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">Suggested Organizations</h5>
                                    <a href="{{ url('suggest-new-resources') }}" class="textLink">New suggestion</a>
                                </div>
                                @if (count($suggestedOrganizations))
                                    <ul class="list-unstyled mb-0">
                                        @foreach ($suggestedOrganizations as $suggestion)
                                            <li class="py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="me-3">
                                                        <div class="fw-semibold sz-16">{{ $suggestion->name ?? 'Untitled organization' }}
                                                        </div>
                                                        <div class="text-muted small">
                                                            {{ ucfirst($suggestion->suggestion_type ?? 'new') }} suggestion &middot;
                                                            Status: {{ ucfirst($suggestion->status ?? 'pending') }} &middot;
                                                            Type: {{ $suggestion->type == 'government' ? 'Government' : 'Non-Government' }}
                                                            @if ($suggestion->created_at)
                                                                &middot; {{ $suggestion->created_at->format('M j, Y') }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if ($suggestion->organization_id)
                                                        <a href="{{ url('/organization-details/' . $suggestion->organization_id) }}"
                                                            class="textLink">View</a>
                                                    @endif
                                                </div>
                                                @if ($suggestion->website)
                                                    <div class="mt-2">
                                                        <a href="{{ $suggestion->website }}" target="_blank" rel="noopener"
                                                            class="textLink text-decoration-none">Website</a>
                                                    </div>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="mb-0 text-muted">You have not suggested any organizations yet. <a
                                            href="{{ url('suggest-new-resources') }}" class="textLink">Submit one now</a>.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
