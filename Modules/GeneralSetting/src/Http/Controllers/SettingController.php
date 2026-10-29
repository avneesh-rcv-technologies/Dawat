<?php

namespace Modules\GeneralSetting\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\GeneralSetting\Repositories\SettingRepository;

class SettingController extends Controller
{
    public function index()
    {
        return view("generalsetting::index");
    }

    public function configuration()
    {
        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        $languages = \Modules\Core\Enums\Language::cases();
        return view("generalsetting::settings.index", compact('timezones', 'languages'));
    }

    public function clearCache()
    {
        try {
            \Artisan::call('optimize:clear');
            if(request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Application cache cleared successfully.',
                    'redirect_url' => url()->current()
                ]);
            }
            return redirect()->back()->with('success', 'Application cache cleared successfully.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function store(Request $request)
    {
        $settings = $request->input('settings', []);

        try {
            if (!is_array($settings)) {
                throw new Exception('Settings must be an array.');
            }

            if ($request->hasFile('company_logo')) {
                fn_upload_image($request->file('company_logo'), 'company_logo', 0, true);
            }
            if ($request->hasFile('company_favicon')) {
                fn_upload_image($request->file('company_favicon'), 'company_favicon', 0, true);
            }

            foreach ($settings as $key => $value) {
                fn_set_setting($key, $value);
            }

            \Artisan::call('optimize:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'Settings have been saved successfully.',
                'redirect_url' => route('settings.configuration')
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'errors' => 'Something went wrong. Please try again'
            ]);
        }
    }

     public function updateSms(Request $request)
    {
        // return $this->processSettings($request, 'SMS / Twilio settings updated successfully!', route('admin.settings.general.sms'));
        return $this->processSettings($request, 'SMS / Twilio settings updated successfully!', route('admin.twilio.configuration'));
    }
}
