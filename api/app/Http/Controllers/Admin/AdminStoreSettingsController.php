<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminStoreSettingsController extends Controller
{
    /**
     * Get all store settings
     */
    public function index(): JsonResponse
    {
        $settings = StoreSetting::getAllSettings();
        $defaults = StoreSetting::getDefaults();

        // Merge with defaults to ensure all keys exist
        $result = array_replace_recursive($defaults, $settings);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Update store settings
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'general' => 'sometimes|array',
            'general.store_name' => 'sometimes|string|max:255',
            'general.favicon_file_id' => 'sometimes|nullable|integer|exists:files,id',
            'general.logo_file_id' => 'sometimes|nullable|integer|exists:files,id',

            'contacts' => 'sometimes|array',
            'contacts.phones' => 'sometimes|array',
            'contacts.phones.*.label' => 'required|string|max:100',
            'contacts.phones.*.number' => 'required|string|max:50',
            'contacts.phones.*.display_type' => 'required|string|in:text,button,button-link,link',
            'contacts.emails' => 'sometimes|array',
            'contacts.emails.*.label' => 'required|string|max:100',
            'contacts.emails.*.email' => 'required|string|max:255',

            'working_hours' => 'sometimes|array',
            'working_hours.weekdays' => 'sometimes|array',
            'working_hours.weekdays.label' => 'sometimes|string|max:50',
            'working_hours.weekdays.from' => 'sometimes|string|max:10',
            'working_hours.weekdays.to' => 'sometimes|string|max:10',
            'working_hours.weekends' => 'sometimes|array',
            'working_hours.weekends.label' => 'sometimes|string|max:50',
            'working_hours.weekends.from' => 'sometimes|string|max:10',
            'working_hours.weekends.to' => 'sometimes|string|max:10',

            'social_links' => 'sometimes|array',
            'social_links.*.platform' => 'required|string|max:50',
            'social_links.*.url' => 'required|url|max:500',
            'social_links.*.name' => 'sometimes|string|max:100',
            'social_links.*.followers' => 'sometimes|nullable|string|max:50',

            'footer_working_hours' => 'sometimes|array',
            'footer_working_hours.weekdays' => 'sometimes|array',
            'footer_working_hours.weekdays.label' => 'sometimes|string|max:50',
            'footer_working_hours.weekdays.from' => 'sometimes|string|max:10',
            'footer_working_hours.weekdays.to' => 'sometimes|string|max:10',
            'footer_working_hours.weekends' => 'sometimes|array',
            'footer_working_hours.weekends.label' => 'sometimes|string|max:50',
            'footer_working_hours.weekends.from' => 'sometimes|string|max:10',
            'footer_working_hours.weekends.to' => 'sometimes|string|max:10',
            'footer_working_hours.phone1' => 'sometimes|array',
            'footer_working_hours.phone1.label' => 'sometimes|nullable|string|max:100',
            'footer_working_hours.phone1.value' => 'sometimes|nullable|string|max:50',
            'footer_working_hours.phone2' => 'sometimes|array',
            'footer_working_hours.phone2.label' => 'sometimes|nullable|string|max:100',
            'footer_working_hours.phone2.value' => 'sometimes|nullable|string|max:50',

            'slides' => 'sometimes|array',
            'slides.*.file_id' => 'required|integer|exists:files,id',
            'slides.*.link' => 'nullable|string|max:500',
        ]);

        // Update each section as separate key
        foreach ($validated as $key => $value) {
            StoreSetting::set($key, $value);
        }

        $settings = StoreSetting::getAllSettings();
        $defaults = StoreSetting::getDefaults();
        $result = array_replace_recursive($defaults, $settings);

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => $result,
        ]);
    }
}
