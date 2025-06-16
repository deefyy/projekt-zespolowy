<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsOrganizer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Zaloguj się, aby kontynuować!');
        }

        $user  = Auth::user();
        $role  = $user->role;


        if ($role === 'organizator' || $role === 'admin') {
            return $next($request);
        }


        return redirect()->back()
            ->with('error', 'Tylko organizator ma dostęp do tej sekcji!');

    }
}
