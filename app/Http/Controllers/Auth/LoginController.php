<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use PragmaRX\Google2FA\Google2FA;
use Stevebauman\Location\Facades\Location;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();

        // Extract client environment details
        $agent = new Agent();
        $device = $agent->isMobile() ? 'Mobile' : ($agent->isTablet() ? 'Tablet' : 'Desktop');
        $browser = ($agent->browser() ?: 'Browser') . ' (' . ($agent->platform() ?: 'OS') . ')';
        $locationStr = 'Localhost / Internal';
        if (!in_array($request->ip(), ['127.0.0.1', '::1', 'localhost'])) {
            try {
                $pos = Location::get($request->ip());
                if ($pos) {
                    $locationStr = trim(($pos->cityName ? $pos->cityName . ', ' : '') . $pos->countryName);
                }
            } catch (\Throwable $e) {}
        }

        if (!$user || !password_verify($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 300);

            if ($user) {
                LoginHistory::create([
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'device' => $device,
                    'browser' => $browser,
                    'location' => $locationStr,
                    'status' => 'failed',
                ]);

                activity()
                    ->causedBy($user)
                    ->log('Failed login attempt recorded from IP: ' . $request->ip());
            }

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        if (!$user->is_active) {
            return back()->withErrors([
                'email' => 'This account has been deactivated. Contact the administrator.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        // Check 2FA
        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put('login.2fa.user_id', $user->id);
            $request->session()->put('login.2fa.remember', $request->filled('remember'));
            return redirect()->route('auth.2fa');
        }

        Auth::login($user, $request->filled('remember'));
        $request->session()->regenerate();

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'device' => $device,
            'browser' => $browser,
            'location' => $locationStr,
            'status' => 'success',
        ]);

        activity()
            ->causedBy($user)
            ->log('User successfully logged into the administration console');

        return redirect()->intended(route('admin.dashboard'));
    }

    public function showTwoFactorForm(Request $request)
    {
        if (!$request->session()->has('login.2fa.user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor');
    }

    public function verifyTwoFactor(Request $request)
    {
        $userId = $request->session()->get('login.2fa.user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => 'required|string|min:6|max:30',
        ]);

        $user = User::findOrFail($userId);
        $google2fa = new Google2FA();

        // 1. Verify 6-digit TOTP key
        $cleanCode = trim(str_replace(['-', ' '], '', $request->code));
        $valid = strlen($cleanCode) === 6 && ctype_digit($cleanCode) 
            ? $google2fa->verifyKey($user->two_factor_secret, $cleanCode) 
            : false;

        // 2. If TOTP fails, check recovery backup codes
        if (!$valid && !empty($user->two_factor_recovery_codes)) {
            $codes = json_decode($user->two_factor_recovery_codes, true) ?: [];
            $inputFormatted = strtoupper(trim($request->code));
            foreach ($codes as $idx => $storedCode) {
                if (strtoupper($storedCode) === $inputFormatted || strtoupper(str_replace('-', '', $storedCode)) === strtoupper(str_replace('-', '', $inputFormatted))) {
                    unset($codes[$idx]);
                    $user->two_factor_recovery_codes = json_encode(array_values($codes));
                    $user->save();
                    $valid = true;
                    break;
                }
            }
        }

        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid authentication token or recovery code.']);
        }

        $remember = $request->session()->get('login.2fa.remember', false);
        $request->session()->forget(['login.2fa.user_id', 'login.2fa.remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $agent = new Agent();
        $device = $agent->isMobile() ? 'Mobile' : ($agent->isTablet() ? 'Tablet' : 'Desktop');
        $browser = ($agent->browser() ?: 'Browser') . ' (' . ($agent->platform() ?: 'OS') . ')';

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'device' => $device,
            'browser' => $browser,
            'location' => '2FA Verified',
            'status' => 'success_2fa',
        ]);

        activity()
            ->causedBy($user)
            ->log('User verified Two-Factor Authentication (2FA) and logged in');

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            activity()
                ->causedBy(Auth::user())
                ->log('User logged out from session');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
