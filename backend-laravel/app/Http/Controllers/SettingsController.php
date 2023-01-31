<?php

namespace App\Http\Controllers;

use App\Models\Settings;

class SettingsController extends Controller
{
    public function settings()
    {
        $settings = Settings::all();

        return response($settings);

    }
}
