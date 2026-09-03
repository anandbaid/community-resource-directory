{{--
    Root template for admin Inertia screens shown to a signed-out visitor.

    Same as backend.layouts.inertia without the header and sidebar, which are
    built from the authenticated user and have nothing to show here.
--}}
@include('backend.includes.head')

<main class="app-main">
    @inertia
</main>

@push('custom-scripts')
    @vite(['resources/js/admin.js'])
@endpush

@include('backend.includes.foot')
