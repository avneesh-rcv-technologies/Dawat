<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        return view('website::pages.home');
    }

    public function about(Request $request)
    {
        return view('website::pages.about');
    }

    public function howItWorks(Request $request)
    {
        return view('website::pages.how-it-works');
    }

    public function blogs(Request $request)
    {
        return view('website::pages.blogs');
    }

    public function blogDetail(Request $request, string $slug)
    {
        abort_unless($slug === 'the-sweet-truth-about-boxed-cake-mix', 404);

        return view('website::pages.blog-detail');
    }

    public function contact(Request $request)
    {
        return view('website::pages.contact');
    }

    public function partner(Request $request)
    {
        return view('website::pages.partner');
    }
}
