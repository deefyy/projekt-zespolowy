<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (is_null($user->email_verified_at)) {

            return redirect()->route('verification.notice')
                ->with('error', 'Najpierw potwierdź adres e-mail!');

        }

        return $next($request);
    }
}
