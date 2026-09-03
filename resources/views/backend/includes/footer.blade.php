{{-- Modals  --}}
@stack('modals')
<footer class="app-footer"> <!--begin::To the end-->
    <!--begin::Copyright-->
    @php
        $copyRight = App\Models\SiteSettings::where('settings_name', 'copy_right')->first()->settings_value ?? '';
    @endphp
    {!! $copyRight !!}
    <!--end::Copyright-->
</footer> <!--end::Footer-->
