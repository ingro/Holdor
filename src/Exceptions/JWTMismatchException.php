<?php


namespace Ingruz\Holdor\Exceptions;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class JWTMismatchException extends \Exception
{
    public function render(Request $request): JsonResponse
    {
        return Response::json(['error' => 'auth'], 401);
    }
}
