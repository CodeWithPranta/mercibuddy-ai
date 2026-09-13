<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Setting;

class DefaultPagesController extends Controller
{
    public function index($slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        $setting = Setting::first();

        return view('page', compact('page', 'setting'));
    }
}
