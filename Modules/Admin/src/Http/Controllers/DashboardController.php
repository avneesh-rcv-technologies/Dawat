<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('admin::pages.dashboard.index', [
            'userName' => $request->user()?->name ?? '',
        ]);
    }
}
