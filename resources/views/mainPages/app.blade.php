@extends('root', ['cssClass' => 'font-sans text-ink bg-gray-50'])


@section('childContent')

    @include('partials.header')
    
    @yield('content')

    @section('footer')
        @include('partials.footer')
    @show

@endsection
