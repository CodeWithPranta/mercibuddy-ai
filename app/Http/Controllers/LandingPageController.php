<?php

namespace App\Http\Controllers;

use App\Models\Setting;

class LandingPageController extends Controller
{
    public function index()
    {

        return view('welcome', [
            'setting' => Setting::first(),
        ]);
    }
}
