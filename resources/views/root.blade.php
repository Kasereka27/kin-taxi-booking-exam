<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        <script src="assets/js/tw.js"></script>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', "") | {{ config('app.name', 'Laravel') }}</title>
    </head>
    <body>

        @yield('childContent')

    </body>
</html>
