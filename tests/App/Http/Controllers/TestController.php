<?php


namespace App\Http\Controllers;


use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;

class TestController extends Controller
{
    public function getSecret(): JsonResponse
    {
        return Response::json(['name' => \Auth::user()->name]);
    }
}
