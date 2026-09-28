<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleOrPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Please sign in to access this section.');
        }

        if (! $user->isActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Your staff account has been deactivated.');
        }

        // If no specific permission specified, simply require active authenticated user
        if (empty($permissions)) {
            return $next($request);
        }

        // Check if user has ANY of the required permissions/roles
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission) || $user->role === $permission) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized. You do not have permission to access this practice module.');
    }
}
