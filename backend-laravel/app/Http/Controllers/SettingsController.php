<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;

final class SettingsController extends Controller
{
    public function settings()
    {
        $settings = Settings::all();

        return response($settings);
    }

    public function saveSettings(Request $request)
    {
        Settings::find(1)->update([
            'show_followers' => $request->input('show_followers'),
            'show_friends'   => $request->input('show_friends'),
            'task_timeout'   => $request->input('task_timeout')
        ]);
    }
}
