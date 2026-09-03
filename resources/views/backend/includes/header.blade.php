@php $user= auth()->user() @endphp
<!--begin::Header-->
<nav class="app-header navbar navbar-expand bg-body"> <!--begin::Container-->
    <div class="container-fluid"> <!--begin::Start Navbar Links-->
        <ul class="navbar-nav">
            <li class="nav-item"> <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"> <i
                        class="fa-solid fa-bars"></i> </a> </li>
        </ul> <!--end::Start Navbar Links--> <!--begin::End Navbar Links-->
        <ul class="navbar-nav ms-auto">
            <li class="nav-item d-flex align-items-center me-3">
                <form action="{{ route('admin.clear-cache') }}" method="POST" class="d-flex align-items-center">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        <span class="d-none d-md-inline">Clear cache</span>
                    </button>
                </form>
            </li>
            <li class="nav-item dropdown user-menu"> <a href="#" class="nav-link dropdown-toggle"
                    data-bs-toggle="dropdown"> <img
                        src="{{ asset(!empty($user->profile_pic) ? $user->profile_pic : '/admin-assets/assets/img/avatar.jpg') }}"
                        class="user-image rounded-circle shadow" alt="User Image"> <span
                        class="d-none d-md-inline">{{ $user->name }}</span> </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end"> <!--begin::User Image-->
                    <li class="user-footer">
                        <a href="{{ route('admin.profile') }}" class="btn btn-default btn-flat">Profile</a>
                        <a href="{{ url('admin/logout') }}" class="btn btn-default btn-flat float-end">Sign out</a>
                    </li>
                    <!--end::Menu Footer-->
                </ul>
            </li> <!--end::User Menu Dropdown-->
        </ul> <!--end::End Navbar Links-->
    </div> <!--end::Container-->
</nav> <!--end::Header-->
