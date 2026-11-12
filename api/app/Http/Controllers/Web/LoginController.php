<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Browser login for /oauth/authorize (Authorization Code + PKCE, see DECISIONS.md 005).
 * Sign-in sends the user back to the intended authorize URL; the first-party admin client skips consent.
 */
class LoginController extends Controller
{
    public function show(): View|RedirectResponse
    {
        return Auth::guard('web')->check() ? redirect()->intended(url('/')) : view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->attempt($credentials)) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(url('/'));
    }

    /** Ends the browser session and returns to the admin app's signed-out page. */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $admin = parse_url((string) config('access.oauth_admin_client.redirect_uri'));

        return redirect(($admin['scheme'] ?? 'http').'://'.($admin['host'] ?? 'localhost').(isset($admin['port']) ? ':'.$admin['port'] : '').'/signed-out');
    }
}
