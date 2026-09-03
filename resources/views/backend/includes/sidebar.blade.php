<!--begin::Sidebar-->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark"> <!--begin::Sidebar Brand-->
    <div class="sidebar-brand"> <!--begin::Brand Link-->
        <a href="{{ route('admin.dashboard') }}" class="brand-link">
            <span class="brand-text fw-light">Community Resource Directory</span> <!--end::Brand Text-->
        </a> <!--end::Brand Link-->
    </div> <!--end::Sidebar Brand-->
    <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
        <nav class="mt-2"> <!--begin::Sidebar Menu-->
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.category.index') }}"
                        class="nav-link {{ request()->routeIs('admin.category.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-tags"></i>
                        <p>
                            Category
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.user.index') }}"
                        class="nav-link {{ request()->routeIs('admin.user.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-people-fill"></i>
                        <p>
                            Users
                        </p>
                    </a>
                </li>
                <li
                    class="nav-item {{ request()->routeIs('admin.organization.*') || request()->routeIs('admin.bulk-import') || request()->routeIs('admin.saved-searches.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-buildings"></i>
                        <p>
                            Organizations
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.organization.index') }}"
                                class="nav-link {{ request()->routeIs('admin.organization.*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-list-ul"></i>
                                <p>Organization Lists</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.bulk-import') }}"
                                class="nav-link {{ request()->routeIs('admin.bulk-import') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-cloud-arrow-up"></i>
                                <p>Bulk Import</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.saved-searches.index') }}"
                                class="nav-link {{ request()->routeIs('admin.saved-searches.*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-bookmark-check"></i>
                                <p>Manage Saved Searches</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.spam-report') }}"
                                class="nav-link {{ request()->routeIs('admin.bulk-import') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-graph-up"></i>
                                <p>Organization Report</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.suggested-organizations.index') }}"
                        class="nav-link {{ request()->routeIs('admin.suggested-organizations.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-lightbulb"></i>
                        <p>
                            Suggested Organizations
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.publication.index') }}"
                        class="nav-link {{ request()->routeIs('admin.publication.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-journal-richtext"></i>
                        <p>
                            Publications
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.review.index') }}"
                        class="nav-link {{ request()->routeIs('admin.review.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-star-half"></i>
                        <p>
                            Reviews
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.emailtemplate.index') }}"
                        class="nav-link {{ request()->routeIs('admin.emailtemplate.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-envelope-open"></i>
                        <p>
                            Email Template
                        </p>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('admin.static-pages.*') || request()->routeIs('admin.home-sections') || request()->routeIs('admin.resources') || request()->routeIs('admin.library') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-files"></i>
                        <p>
                            Page Management
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.static-pages.index') }}"
                                class="nav-link {{ request()->routeIs('admin.static-pages.*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-file-earmark-text"></i>
                                <p>Static Pages</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.home-sections') }}"
                                class="nav-link {{ request()->routeIs('admin.home-sections') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-house-door"></i>
                                <p>Home Page Sections</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.resources') }}"
                                class="nav-link {{ request()->routeIs('admin.resources') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-book"></i>
                                <p>Resources Page Sections</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.library') }}"
                                class="nav-link {{ request()->routeIs('admin.library') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-collection"></i>
                                <p>Library Page Sections</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.banner.index') }}"
                        class="nav-link {{ request()->routeIs('admin.banner.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-image"></i>
                        <p>
                            Banner Module
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.queries.index') }}"
                        class="nav-link {{ request()->routeIs('admin.queries.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-chat-square-text"></i>
                        <p>
                            Queries
                        </p>
                    </a>
                </li>
                <li
                    class="nav-item {{ request()->routeIs('admin.generalSettings') || request()->routeIs('admin.profile') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-gear-wide-connected"></i>
                        <p>
                            Settings
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.generalSettings') }}"
                                class="nav-link {{ request()->routeIs('admin.generalSettings') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-gear"></i>
                                <p>Site Settings</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.profile') }}"
                                class="nav-link {{ request()->routeIs('admin.profile') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-person-gear"></i>
                                <p>Profile</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ url('admin/logout') }}" class="nav-link">
                        <i class="nav-icon bi bi-box-arrow-right"></i>
                        <p>
                            Log out
                        </p>
                    </a>
                </li>
            </ul> <!--end::Sidebar Menu-->
        </nav>
    </div> <!--end::Sidebar Wrapper-->
</aside> <!--end::Sidebar-->
