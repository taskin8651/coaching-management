<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappSetting;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WhatsappSettingsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('whatsapp_settings_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = WhatsappSetting::latest()->get();

        return view('admin.whatsappSettings.index', compact('settings'));
    }

    public function create()
    {
        abort_if(Gate::denies('whatsapp_settings_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.whatsappSettings.create');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('whatsapp_settings_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        WhatsappSetting::create($this->validated($request));

        return redirect()->route('admin.whatsapp-settings.index')->with('message', 'WhatsApp setting saved successfully.');
    }

    public function edit(WhatsappSetting $whatsappSetting)
    {
        abort_if(Gate::denies('whatsapp_settings_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.whatsappSettings.edit', compact('whatsappSetting'));
    }

    public function update(Request $request, WhatsappSetting $whatsappSetting)
    {
        abort_if(Gate::denies('whatsapp_settings_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $whatsappSetting->update($this->validated($request));

        return redirect()->route('admin.whatsapp-settings.index')->with('message', 'WhatsApp setting updated successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'api_provider' => ['nullable', 'string', 'max:255'],
            'api_base_url' => ['nullable', 'url', 'max:1000'],
            'api_key' => ['nullable', 'string'],
            'sender_number' => ['nullable', 'string', 'max:255'],
            'biometric_device_token' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
