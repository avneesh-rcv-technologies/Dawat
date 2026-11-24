<?php

namespace Modules\GeneralSetting\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
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
        $request->validate([
            'default_image' => fn_image_validation_rules(),
        ], fn_image_validation_messages('default_image'));

        $settings = $request->input('settings', []);

        if (is_array($settings)) {
            $settings = app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->prepareForSave($settings);
        }

        try {
            if (!is_array($settings)) {
                throw new Exception('Settings must be an array.');
            }

            // Platform commission must be a percentage between 0 and 100.
            if (array_key_exists('general.platform_commission_percentage', $settings)) {
                $commission = $settings['general.platform_commission_percentage'];
                if ($commission === null || $commission === '') {
                    $settings['general.platform_commission_percentage'] = 10;
                } elseif (!is_numeric($commission) || $commission < 0 || $commission > 100) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['settings.general.platform_commission_percentage' => ['Platform commission must be a percentage between 0 and 100.']],
                    ], 422);
                } else {
                    $settings['general.platform_commission_percentage'] = round((float) $commission, 2);
                }
            }

            if ($request->hasFile('company_logo')) {
                fn_upload_image($request->file('company_logo'), 'company_logo', 0, true);
            }
            if ($request->hasFile('company_favicon')) {
                fn_upload_image($request->file('company_favicon'), 'company_favicon', 0, true);
            }

            fn_upload_request_file($request, 'default_image', 'default_image', 0, true);

            foreach ($settings as $key => $value) {
                fn_set_setting($key, $value);
            }

            \Artisan::call('optimize:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'Settings have been saved successfully.',
                'redirect_url' => route('settings.configuration')
            ]);
        } catch (ValidationException $e) {
            throw $e;
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
