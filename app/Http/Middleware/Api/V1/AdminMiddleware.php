<?php

namespace App\Http\Middleware\Api\V1;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Api\V1\ResponseResource;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return (new ResponseResource([
                'status'      => 'error',
                'status_code' => Response::HTTP_FORBIDDEN,
                'message'     => 'Unauthorized access.',
                'data'        => null,
                'errors'      => null,
            ]))->response()->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $tokenAbilities = $request->user()->currentAccessToken()?->abilities ?? null;

        if (in_array('*', $tokenAbilities)) {
            return $next($request);
        }

        $routePrefix = $request->route()?->getPrefix();

        if (!$tokenAbilities || !in_array($routePrefix, $tokenAbilities)) {
            return response()->json([
                'status' => 'error',
                'status_code' => 403,
                'message' => 'Access denied: insufficient permissions for this route.',
                'data' => null,
                'errors' => null
            ], 403);
        }

        return $next($request);
    }
}
