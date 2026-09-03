{{--
    Root template for Inertia (Vue) pages on the public site.

    Only the authenticated account screens use this; the marketing and directory
    pages stay server rendered and use Vue islands instead. It reuses the same
    frontend chrome so converted and unconverted pages are indistinguishable.
--}}
{{--
    Pages opt out of the light page background the same way a Blade view would,
    by leaving the section empty — here it is driven by a page prop because the
    section has to be set before the header is included.
--}}
@section('light-back', ($page['props']['lightBack'] ?? true) ? 'light-back' : '')

@include('frontend.includes.head')
@include('frontend.includes.header')

@inertia

@include('frontend.includes.footer')
@include('frontend.includes.foot')
