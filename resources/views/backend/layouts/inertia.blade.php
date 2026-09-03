{{--
    Root template for Inertia (Vue) admin screens.

    It reuses the exact AdminLTE chrome the Blade admin pages use, so converted
    and unconverted screens look identical and can link to each other. Only the
    <main> body is owned by Vue.
--}}
@include('backend.includes.head')

@include('backend.includes.header')

@include('backend.includes.sidebar')

<main class="app-main">
    @inertia
</main>

@include('backend.includes.footer')

@push('custom-scripts')
    @vite(['resources/js/admin.js'])
@endpush

@include('backend.includes.foot')
