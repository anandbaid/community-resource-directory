@extends('backend.layouts.blank')

@section('title', ' | Login')
@section('content')
    <div class="app-content d-flex justify-content-center align-items-center h-100"> <!--begin::Container-->
        <div class="container-fluid w-100"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100 maxw-5">
                    <div class="d-table-cell align-middle">
                        <div class="text-center mt-4">
                            <h1 class="h2">Login</h1>
                            <p class="lead">
                                Login with admin credentials to continue
                            </p>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="m-sm-3">
                                    <form name="loginForm" action="{{ route('admin.login') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="redirect" value="{{ $redirect }}">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input class="form-control form-control-lg" type="email"
                                                name="administrator_email" value="{{ old('administrator_email') }}"
                                                placeholder="Enter your email" required />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <input class="form-control form-control-lg" type="password"
                                                name="administrator_password" placeholder="Enter your password" required />
                                        </div>
                                        {{-- <div>
                                    <div class="form-check align-items-center">
                                        <input id="rememberMe" type="checkbox" class="form-check-input" value="1"
                                            name="remember">
                                        <label class="form-check-label text-small" for="rememberMe">Remember
                                            me</label>
                                    </div>
                                </div> --}}
                                        <?php if (isset($msg)) { ?>
                                        <div class="alert alert-warning" role="alert">
                                            <?php echo $msg; ?>
                                        </div>
                                        <?php } ?>
                                        <div class="d-grid gap-2 mt-3">
                                            <button type="submit" class="btn btn-lg btn-primary">Sign in</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!--end::Container-->
    </div> <!--end::App Content-->
@endsection

@push('custom-scripts')
    <script>
        window.addEventListener('load', function() {
            $('form[name=loginForm]').validate();
        })
    </script>
@endpush
