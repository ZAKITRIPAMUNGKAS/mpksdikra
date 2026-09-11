<?php

namespace App\Http\Middleware;

use App\Models\Event;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEventAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $param = $request->route('event');
        $event = $param instanceof Event ? $param : Event::find($param);

        if (! $event) {
            abort(404);
        }

        if ($user->isAdmin() || $user->isExecutive()) {
            return $next($request);
        }

        if ($user->isFasilitator() && $event->fasilitator()->whereKey($user->id)->exists()) {
            return $next($request);
        }

        abort(403);
    }
}
