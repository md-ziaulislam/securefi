<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use PragmaRX\Google2FA\Google2FA;

class ProfileController extends Controller
{
    /**
     * Display the user's profile and security settings console.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $has2fa = $user->hasTwoFactorEnabled();

        $google2fa = new Google2FA();

        // If 2FA is not enabled, generate a secret for setup
        $secret = null;
        $qrCodeUrl = null;
        if (!$has2fa) {
            $secret = $request->session()->get('2fa_setup_secret');
            if (!$secret) {
                $secret = $google2fa->generateSecretKey();
                $request->session()->put('2fa_setup_secret', $secret);
            }
            $qrCodeUrl = $google2fa->getQRCodeUrl('SecuroFi.Tech', $user->email, $secret);
        }

        // Decode stored recovery codes if active
        $recoveryCodes = [];
        if ($has2fa && !empty($user->two_factor_recovery_codes)) {
            $recoveryCodes = json_decode($user->two_factor_recovery_codes, true) ?: [];
        }

        // Recent login audit logs for this user
        $loginHistories = LoginHistory::where('user_id', $user->id)
            ->latest('created_at')
            ->take(10)
            ->get();

        // All standard timezones for profile preference
        $allTimezones = app(\App\Services\TimezoneService::class)->getAllTimezones();

        return view('admin.profile.index', compact(
            'user',
            'has2fa',
            'secret',
            'qrCodeUrl',
            'recoveryCodes',
            'loginHistories',
            'allTimezones'
        ));
    }

    /**
     * Update the user's personal profile information.
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email,' . $user->id,
            'timezone' => 'nullable|string|max:64',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp',
        ]);

        if ($request->boolean('remove_avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = null;
        } elseif ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->bio = $validated['bio'] ?? null;
        if ($request->filled('timezone')) {
            $user->timezone = $validated['timezone'];
        }
        $user->save();

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log('User updated their profile details');

        return redirect()->route('admin.profile.index')->with('success', 'Profile information updated successfully.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'min:8', 'confirmed', Password::defaults()],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match our records.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log('User changed their account password');

        return redirect()->route('admin.profile.index')->with('success', 'Password successfully changed.');
    }

    /**
     * Confirm and activate Two-Factor Authentication (Turn ON).
     */
    public function confirmTwoFactor(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'secret' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($request->secret, $request->code);

        if (!$valid) {
            return back()->withErrors([
                'two_factor_code' => 'Invalid verification code. Please make sure your device clock is accurate and try again.'
            ])->with('open_2fa_setup', true);
        }

        // Generate 8 cryptographically secure recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = strtoupper(bin2hex(random_bytes(4))) . '-' . strtoupper(bin2hex(random_bytes(4)));
        }

        $user->two_factor_secret = $request->secret;
        $user->two_factor_recovery_codes = json_encode($recoveryCodes);
        $user->two_factor_confirmed_at = now();
        $user->save();

        $request->session()->forget('2fa_setup_secret');

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log('User activated Two-Factor Authentication (2FA)');

        return redirect()->route('admin.profile.index')
            ->with('success', 'Two-Factor Authentication (2FA) is now active on your account!')
            ->with('show_recovery_codes', $recoveryCodes);
    }

    /**
     * Disable Two-Factor Authentication (Turn OFF).
     */
    public function disableTwoFactor(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'disable_password' => 'required|string',
        ]);

        if (!Hash::check($request->disable_password, $user->password)) {
            return back()->withErrors([
                'disable_password' => 'The password you entered is incorrect. 2FA was not disabled.'
            ]);
        }

        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        $request->session()->forget('2fa_setup_secret');

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log('User deactivated Two-Factor Authentication (2FA)');

        return redirect()->route('admin.profile.index')
            ->with('success', 'Two-Factor Authentication (2FA) has been deactivated.');
    }

    /**
     * Regenerate new set of 2FA recovery backup codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('admin.profile.index')
                ->withErrors(['2fa' => 'Two-Factor Authentication must be enabled to regenerate recovery codes.']);
        }

        $request->validate([
            'regen_password' => 'required|string',
        ]);

        if (!Hash::check($request->regen_password, $user->password)) {
            return back()->withErrors([
                'regen_password' => 'Incorrect password verification. Unable to regenerate recovery codes.'
            ]);
        }

        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = strtoupper(bin2hex(random_bytes(4))) . '-' . strtoupper(bin2hex(random_bytes(4)));
        }

        $user->two_factor_recovery_codes = json_encode($recoveryCodes);
        $user->save();

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log('User regenerated 2FA recovery codes');

        return redirect()->route('admin.profile.index')
            ->with('success', 'New recovery codes generated. Previous codes are now expired.')
            ->with('show_recovery_codes', $recoveryCodes);
    }
}
