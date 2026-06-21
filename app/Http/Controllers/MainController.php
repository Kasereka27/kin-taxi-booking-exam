<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainController extends Controller
{

    public function home()
    {
        return view('pageContent.home');
    }

    public function reservation()
    {
        return view('pageContent.reservation');
    }

    public function suivi()
    {
        return view('pageContent.suivi');
    }
    public function tarifs()
    {
        return view('pageContent.tarifs');
    }
    public function about()
    {
        return view('pageContent.a-propos');
    }
    public function contact()
    {
        return view('pageContent.contact');
    }
}
