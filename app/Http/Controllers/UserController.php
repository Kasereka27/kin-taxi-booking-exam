<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function dashboardDriver()
    {
        return view('pageContent.dashboardDriver');
    }
    public function dashboardClient()
    {
        return view('pageContent.dashboardClient');
    }
}
