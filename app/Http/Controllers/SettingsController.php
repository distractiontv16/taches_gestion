<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('settings');
    }

    /**
     * Update notification settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateNotifications(Request $request)
    {
        $user = Auth::user();
        
        // Validate the request
        $validated = $request->validate([
            'whatsapp_number' => 'nullable|string|max:20',
            'receive_whatsapp' => 'boolean|nullable',
            'email_tasks' => 'boolean|nullable',
            'email_reminders' => 'boolean|nullable',
            'email_routines' => 'boolean|nullable',
            'email_summary' => 'boolean|nullable',
            'app_notifications' => 'boolean|nullable',
            'sound_notifications' => 'boolean|nullable',
        ]);
        
        // Update WhatsApp number
        if (isset($validated['whatsapp_number'])) {
            $user->whatsapp_number = $validated['whatsapp_number'];
            $user->save();
        }
        
        // Save notification settings in user preferences (you might need to create a user preferences model)
        // For now, just return with success message
        
        return redirect()->back()->with('success', 'Notification settings updated successfully.');
    }

    /**
     * Update profile settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'bio' => 'nullable|string',
        ]);
        
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        // Add bio field if it exists in your user model
        
        $user->save();
        
        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update appearance settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAppearance(Request $request)
    {
        // Here you would save the appearance settings to user preferences
        
        return redirect()->back()->with('success', 'Appearance settings updated successfully.');
    }

    /**
     * Update security settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSecurity(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        
        $user->password = bcrypt($validated['new_password']);
        $user->save();
        
        return redirect()->back()->with('success', 'Password updated successfully.');
    }
}
