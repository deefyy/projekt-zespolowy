<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\DisableTwoFactorNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class TwoFactorDisableController extends Controller
{
    // Send the email with the signed link
    public function sendLink(Request $request)
    {
        // Capture the email from the request
        $email = $request->input('email');

        // Validate the email
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Store the email in the session
        Session::put('disable_2fa_email', $email);

        // Send the notification to the user's email
        $user = User::where('email', $email)->first();
        if ($user) {
            $user->notify(new DisableTwoFactorNotification());
            return back()->with('status', 'Wysłaliśmy link do wyłączenia dwuetapowego uwierzytelniania na Twój adres e-mail.');
        }

        return back()->withErrors(['email' => 'User not found.']);
    }

    // Handle the signed link
    public function disable(Request $request, $id, $hash)
    {
        
        $user = User::findOrFail($id);

        // Verify the hash matches the user's email
        if (! hash_equals(sha1($user->email), $hash)) {
            abort(403, 'Invalid hash');
        }

        // Disable two-factor authentication
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
        
        return redirect()->route('login')->with('status', 'Dwuetapowe uwierzytelnianie zostało wyłączone.');
    }
}
