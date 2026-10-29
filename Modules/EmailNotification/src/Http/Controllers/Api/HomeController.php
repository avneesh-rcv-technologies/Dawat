<?php

namespace Modules\EmailNotification\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'message' => 'Welcome to the EmailNotification API!'
        ]);
    }
} 