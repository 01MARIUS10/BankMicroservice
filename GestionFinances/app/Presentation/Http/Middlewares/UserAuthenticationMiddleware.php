<?php

namespace App\Presentation\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use App\Presentation\Http\Responses\ApiResponse;

class UserAuthenticationMiddleware
{

public function __construct(
        private readonly ApiResponse $apiResponse,
    ) {}
    public function handle(Request $request,Closure $next)
    {
        if (!$request->header('X-User-Id')) {
            return $this->apiResponse->unAuthorize();
        }

        return $next($request);
    }
}
