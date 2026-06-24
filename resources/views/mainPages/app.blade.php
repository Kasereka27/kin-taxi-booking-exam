@extends('root', ['cssClass' => 'font-sans text-ink bg-gray-50'])


@section('childContent')

    @section('header')
        @include('partials.header')
    @show

    <main class="flex-1">
        @yield('content')
    </main>

    @section('footer')
        @include('partials.footer')
    @show

@endsection
