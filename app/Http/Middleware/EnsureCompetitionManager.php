<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Competition;
use Illuminate\Support\Facades\Auth;

class EnsureCompetitionManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 0. musi być zalogowany
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        /** @var App\Models\User $user */
        $user = Auth::user();

        /** @var Competition|null $competition */
        $competition = $request->route('competition');

        if (! $competition) {
            abort(404);
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if ($competition->user_id === $user->id) {
            return $next($request);
        }

        $isCo = $competition->coOrganizers()
                            ->where('user_id', $user->id)
                            ->exists();

        if ($isCo) {
            return $next($request);
        }

        abort(403, 'Brak dostępu do tego konkursu.');
    }
}
