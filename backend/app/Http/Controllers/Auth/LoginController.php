<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Session login in Blade. The session cookie it creates is the one the
 * Angular elements reuse to call /api through Sanctum (stateful API).
 */
class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Wrong credentials: back to the form with a Catalan error and the email
     * kept. Right credentials: new session id and redirect to the clients area.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => __('auth.failed'),
            ])->withInput($request->only('email'));
        }
        $request->session()->regenerate();

        return redirect()->intended(route('quotes.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
