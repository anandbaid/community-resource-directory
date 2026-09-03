@extends('frontend.layouts.app')
@section('title', ' | Profile')
@section('light-back', 'light-back')
@section('content')
    <main>
        <!-- banner start -->
        <section>
            <div class="bannerBack innerBanner" style="background-image: url({{ asset('assets/img/banner.png') }});">
                <div class="container banContainer">
                    <div class="df-column bannerCont" data-aos="fade-up">
                        <h2 class="bannerHead">Profile</h2>
                        <p class="banPara">Review your details and keep your information current.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- profile content -->
        <section>
            <div class="container panel bg-container panel-bottom">
                <div class="row gy-4 gx-4">
                    <div class="col-lg-3" data-aos="fade-up">
                        @include('frontend.includes.quick-links')
                    </div>
                    <div class="col-lg-9">
                        <div class="row gy-4">
                            <div class="col-lg-6" data-aos="fade-up">
                                <div class="card shadow-sm border-0 h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <p class="text-uppercase text-muted small mb-1">Signed in as</p>
                                                <h4 class="mb-0">{{ $user->name }}</h4>
                                                <p class="text-muted mb-0">{{ $user->email }}</p>
                                            </div>
                                            <a href="{{ route('user.dashboard') }}" class="textLink text-decoration-none">Back to dashboard</a>
                                        </div>
                                        <div class="row gy-3">
                                            <div class="col-sm-6">
                                                <p class="text-muted small mb-1">First Name</p>
                                                <p class="mb-0">{{ $user->first_name ?? 'Not provided' }}</p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p class="text-muted small mb-1">Last Name</p>
                                                <p class="mb-0">{{ $user->last_name ?? 'Not provided' }}</p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p class="text-muted small mb-1">Phone</p>
                                                <p class="mb-0">
                                                    {{ $user->phone ? \App\Http\Controllers\CommonFunction::formatPhone($user->phone) : 'Not provided' }}
                                                </p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p class="text-muted small mb-1">Zip Code</p>
                                                <p class="mb-0">{{ $user->zipcode ?? 'Not provided' }}</p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p class="text-muted small mb-1">Account Status</p>
                                                <p class="mb-0 text-capitalize">{{ $user->status ?? 'Active' }}</p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p class="text-muted small mb-1">Member Since</p>
                                                <p class="mb-0">{{ optional($user->created_at)->format('F j, Y') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                                <div class="card shadow-sm border-0 h-100">
                                    <div class="card-body">
                                        <h5 class="mb-3">Edit Profile</h5>
                                        @if ($errors->any())
                                            <div class="alert alert-danger">
                                                <ul class="mb-0">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        <form method="POST" action="{{ route('user.profile.update') }}" name="user_profile"
                                            id="user_profile">
                                            @csrf
                                            <div class="row gy-3">
                                                <div class="col-sm-6">
                                                    <label class="text-muted small mb-1" for="first_name">First Name</label>
                                                    <input type="text" id="first_name" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}" required>
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="text-muted small mb-1" for="last_name">Last Name</label>
                                                    <input type="text" id="last_name" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}">
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="text-muted small mb-1" for="phone">Phone</label>
                                                    <input type="tel" id="phone" name="phone"
                                                        class="form-control phone-mask"
                                                        value="{{ old('phone', \App\Http\Controllers\CommonFunction::formatPhone($user->phone)) }}"
                                                        placeholder="Enter Phone No">
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="text-muted small mb-1" for="zipcode">Zip Code</label>
                                                    <input type="text" id="zipcode" name="zipcode" class="form-control" value="{{ old('zipcode', $user->zipcode) }}" placeholder="Enter Zip Code">
                                                </div>
                                                <div class="col-12 d-flex justify-content-end">
                                                    <button type="submit" class="primary-btn">Update Profile</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12" data-aos="fade-up" data-aos-delay="200">
                                <div class="card shadow-sm border-0">
                                    <div class="card-body">
                                        <h5 class="mb-3">Account Snapshot</h5>
                                        <ul class="list-unstyled mb-0">
                                            <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                <span>Saved resources</span>
                                                <strong>{{ $savedResourcesCount }}</strong>
                                            </li>
                                            <li class="d-flex justify-content-between align-items-center py-2">
                                                <span>Saved searches</span>
                                                <strong>{{ $savedSearchCount }}</strong>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
@push('custom-scripts')
    <script>
        (function() {
            const form = document.getElementById('user_profile');
            if (!form) return;

            const rules = {
                phone: {
                    phoneNo: true
                },
                zipcode: {
                    postalCode: true
                }
            };

            form.addEventListener('submit', function(e) {
                const validator = validateForm($(form), rules, {});
                sanitizePhoneInputs(form);
                if (!validator.form()) {
                    e.preventDefault();
                }
            });
        })();
    </script>
@endpush
