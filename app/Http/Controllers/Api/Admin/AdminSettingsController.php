<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::all()->pluck('value', 'key');
        return response()->json(['settings' => $settings]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'currency_code'          => ['sometimes', 'string', 'max:10'],
            'currency_symbol'        => ['sometimes', 'string', 'max:10'],
            'currency_position'      => ['sometimes', 'in:before,after'],
            'announcement_enabled'   => ['sometimes', 'in:0,1'],
            'announcement_bg'        => ['sometimes', 'string', 'max:20'],
            'announcement_text'      => ['sometimes', 'string', 'max:20'],
            'announcement_messages'  => ['sometimes', 'string'],
            'hero_settings'          => ['sometimes', 'string'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        $settings = Setting::all()->pluck('value', 'key');
        return response()->json(['settings' => $settings]);
    }
}
