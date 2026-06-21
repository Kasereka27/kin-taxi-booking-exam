<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
         <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', "") | {{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/map.js'])
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    </head>
    <body @isset($cssClass) class="{{ $cssClass }}" @endisset>
        
        @yield('childContent')

    </body>
</html>
