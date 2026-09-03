@include('backend.includes.head')


@include('backend.includes.header')

@include('backend.includes.sidebar')

<main class="app-main">
    <h1 class="h3 mb-3">@yield('page-title') </h1>
    @yield('content')
</main>

@include('backend.includes.footer')
@include('backend.includes.foot')
