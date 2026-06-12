<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('user_id')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Voce precisa estar logado para acessar este recurso.',
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Voce precisa estar logado para acessar esta pagina.');
        }

        return $next($request);
    }
}
