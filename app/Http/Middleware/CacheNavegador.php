<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheNavegador
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo cachea recursos estáticos
        if ($request->is('build/*', 'storage/*', '*.css', '*.js', '*.woff', '*.woff2', '*.png', '*.jpg', '*.svg')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        }

        return $response;
    }
}