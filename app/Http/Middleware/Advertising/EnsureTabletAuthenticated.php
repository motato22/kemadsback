<?php

namespace App\Http\Middleware\Advertising;

use App\Models\Advertising\AdvTablet;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que verifica que el token Sanctum corresponde a una tablet activa.
 * Añade la tablet al request como usuario autenticado para los controladores.
 */
class EnsureTabletAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof AdvTablet) {
            return response()->json(['error' => 'Token de tablet no válido.'], 401);
        }

        if ($user->status !== 'active') {
            return response()->json(['error' => 'Tablet inactiva o en aprovisionamiento.'], 403);
        }

        return $next($request);
    }
}
