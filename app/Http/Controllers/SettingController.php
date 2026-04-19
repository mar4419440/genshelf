<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('pages.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except('_token');
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        return redirect()->back()->with('success', __('Settings saved successfully.'));
    }

    public function updateToggles(Request $request)
    {
        $data = $request->except('_token');
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value ? '1' : '0']);
        }
        return redirect()->back()->with('success', __('Toggles updated successfully.'));
    }
    public function setLanguage($lang)
    {
        if (in_array($lang, ['en', 'ar'])) {
            Setting::updateOrCreate(['key' => 'language'], ['value' => $lang]);
        }
        return redirect()->back();
    }
}
