@extends('root', ['cssClass' => 'font-sans text-ink bg-gray-50'])


@section('childContent')

    @section('header')
        @include('partials.header')
    @show

    @yield('content')

    @section('footer')
        @include('partials.footer')
    @show

@endsection
