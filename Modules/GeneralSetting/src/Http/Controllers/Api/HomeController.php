<?php

namespace Modules\GeneralSetting\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'message' => 'Welcome to the GeneralSetting API!'
        ]);
    }

    public function getTermsAndConditions()
    {
        $termsAndConditions = (function_exists('fn_get_setting')) ? fn_get_setting('general.company.terms_and_conditions') : null;
        return response()->json([
            'terms_and_conditions' => $termsAndConditions ?? '',
        ]);
    }
}