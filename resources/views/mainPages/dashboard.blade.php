@extends('root', ['cssClass' => 'font-sans text-ink bg-gray-50 flex flex-col h-dvh max-h-dvh overflow-hidden'])

@section('childContent')

    @section('header')
    @show

    <div class="h-full min-h-0 overflow-hidden">
        @yield('content')
    </div>

    @section('footer')
    @show

@endsection
