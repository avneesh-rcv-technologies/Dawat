<?php

namespace Modules\Faq\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'message' => 'Welcome to the Faq API!'
        ]);
    }
} 