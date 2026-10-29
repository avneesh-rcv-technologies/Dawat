<?php

namespace Modules\DataView\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'message' => 'Welcome to the DataView module!'
        ]);
    }

    public function export() {
         return view('dataview::components.dataView.partials.export');
    }
} 