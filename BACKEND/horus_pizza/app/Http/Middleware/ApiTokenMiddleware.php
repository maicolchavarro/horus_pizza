<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Login;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        $login = Login::where('api_token', $token)->first();

        if (!$login) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        // Opcional: podrías adjuntar el login/empleado al request
        $request->attributes->set('login_user', $login);

        return $next($request);
    }
}
