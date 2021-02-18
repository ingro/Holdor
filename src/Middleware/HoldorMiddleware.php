<?php


namespace Ingruz\Holdor\Middleware;


use Closure;
use Illuminate\Http\Request;
use Ingruz\Holdor\Exceptions\JWTMismatchException;
use Ingruz\Holdor\Helpers\JWTHelper;

class HoldorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->hasHeader('authorization') and ! $request->input('token')) {
            throw new JWTMismatchException('Please provide an Auth Token!');
        }

        $helper = new JWTHelper();

        $tokenString = $request->hasHeader('authorization') ? str_replace('Bearer ', '', $request->header('authorization')) : $request->input('token');

        $token = $helper->verify($tokenString);

        if (! $token) {
            throw new JWTMismatchException('Please provide a valid Auth Token!');
        }

        if ($token->hasClaim('type') && $token->getClaim('type') === 'refresh') {
            throw new JWTMismatchException('You cannot use a Refresh Token!');
        }

        // Auth as the user id included in the token
         \Auth::loginUsingId($token->getClaim('userId'));

        return $next($request);
    }
}
