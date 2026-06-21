@extends('root')

@section('childContent')

    @include('partials.header')
    
    @yield('content')

    @include('partials.footer')

@endsection
