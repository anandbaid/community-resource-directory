</div> <!--end::App Wrapper-->
{{-- Custom scripts  --}}
<script type="text/javascript">
    window.csrf_token = "{{ csrf_token() }}"
</script>
@php
    $assetVersion = $assetVersion ?? cache()->remember('asset_version', 3600, function () {
        return App\Models\SiteSettings::where('settings_name', 'asset_version')->value('settings_value') ?? config('custom.app_version', '1.0.0');
    });
@endphp

<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('/plugins/datatables.net/js/dataTables.bootstrap5.js') }}"></script>
<script src="{{ asset('plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('plugins/popperjs/popper.min.js') }}"></script>
<script src="{{ asset('plugins/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('admin-assets/assets/js/adminlte.js') }}"></script>
<script src="{{ asset('plugins/select2/select2.min.js') }}"></script>
<script src="{{ asset('plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/js/form-handle.js') }}?v={{ $assetVersion }}"></script>
<script src="{{ asset('assets/js/custom.js') }}?v={{ $assetVersion }}"></script>
@include('common.flash')
<script type="text/javascript">
    // Ensure sidebar scrolls instead of overlapping logout/footer when menus expand
    window.addEventListener('load', sidebarScrollFix);
    window.addEventListener('resize', sidebarScrollFix);

    function sidebarScrollFix() {
        const wrapper = document.querySelector('.sidebar-wrapper');
        if (!wrapper) return;
        const rect = wrapper.getBoundingClientRect();
        const available = window.innerHeight - rect.top - 8; // small padding
        wrapper.style.maxHeight = `${available}px`;
        wrapper.style.overflowY = 'auto';
    }
</script>
@stack('custom-scripts')
</body>

</html>
