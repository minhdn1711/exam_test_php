<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>THK Holdings Vietman Hanoi</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;700&display=swap" rel="stylesheet">
        {{-- individual CSS --}}
        @vite('resources/scss/admin/base.scss')
        @vite('resources/scss/admin/side-menu.scss')
        @yield('custom_css')
    </head>
    <body>
        <div class="admin-layout">
            <x-admin.side-menu />
            <main class="admin-main">
                @yield('main_contents')
            </main>
        </div>
        {{-- SweetAlert2 (realrashid/sweet-alert): flashed alerts and data-confirm-delete dialogs --}}
        @sweetAlert
        @yield('page_js')
    </body>
</html>
