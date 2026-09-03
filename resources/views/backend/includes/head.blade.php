<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="title" content="{{ config('app.name') . ' Admin' }}@yield('title')">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" />
    @php
        $assetVersion = cache()->remember('asset_version', 3600, function () {
            return App\Models\SiteSettings::where('settings_name', 'asset_version')->value('settings_value') ??
                config('custom.app_version', '1.0.0');
        });
    @endphp

    <title>{{ config('app.name') . ' Admin' }}@yield('title')</title>
    {{-- Styles  --}}
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('/plugins/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/plugins/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('/plugins/datatables.net/css/dataTables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('/admin-assets/assets/css/adminlte.css') }}?v={{ $assetVersion }}">
    <link href="{{ asset('plugins/select2/select2.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('/admin-assets/assets/css/style.css') }}?v={{ $assetVersion }}">
    @stack('custom-styles')
</head>

<!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <div class="loader">
            <div class="lds-ring">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
