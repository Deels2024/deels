<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function GeneralSettings()
    {
        $title = trans('app.general_settings');

        return view('admin.general_settings', compact('title'));
    }

    public function PaymentSettings()
    {
        $title = trans('app.payment_settings');

        return view('admin.payment_settings', compact('title'));
    }

    public function AdSettings()
    {
        $title = trans('app.ad_settings_and_pricing');

        return view('admin.ad_settings', compact('title'));
    }

    public function StorageSettings()
    {
        $title = trans('app.file_storage_settings');

        return view('admin.storage_settings', compact('title'));
    }

    public function SocialSettings()
    {
        $title = trans('app.social_settings');

        return view('admin.social_settings', compact('title'));
    }

    public function reCaptchaSettings()
    {
        $title = trans('app.re_captcha_settings');

        return view('admin.re_captcha_settings', compact('title'));
    }

    public function BlogSettings()
    {
        $title = trans('app.blog_settings');

        return view('admin.blog_settings', compact('title'));
    }

    public function ThemeSettings()
    {
        $title = trans('app.theme_settings');

        return view('admin.theme_settings', compact('title'));
    }

    public function modernThemeSettings()
    {
        $title = trans('app.modern_theme_settings');

        return view('admin.modern_theme_settings', compact('title'));
    }

    public function SocialUrlSettings()
    {
        $title = trans('app.social_url_settings');

        return view('admin.social_url_settings', compact('title'));
    }

    /** Update the specified resource in storage. */
    public function update(Request $request)
    {
        if (config('app.is_demo')) {
            return ['success' => false, 'msg' => trans('app.feature_disable_demo')];
        }

        if ($request->hasFile('ad_popup')) {
            $file = $request->file('ad_popup');

            $customName = 'ad_popup_image.' . $file->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('uploads', $file, $customName);
            $option = Option::firstOrCreate(['option_key' => 'ad_popup_image']);
            $option->option_value = '/uploads/'.$customName;
            $option->save();
        }

        $inputs = array_except($request->input(), ['_token']);

        foreach ($inputs as $key => $value) {
            $option = Option::firstOrCreate(['option_key' => $key]);
            $option->option_value = $value;
            $option->save();
        }
        // check is request comes via ajax?
        if ($request->ajax()) {
            return ['success' => 1, 'msg' => trans('app.settings_saved_msg')];
        }

        return redirect()->back()->with('success', trans('app.settings_saved_msg'));
    }

    public function monetization()
    {
        $title = trans('app.website_monetization');

        return view('admin.website_monetization', compact('title'));
    }
}
