<?php


namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Ingruz\Holdor\Controllers\HoldorController;

class AuthController extends HoldorController
{
    protected function getUserFromRequest(Request $request)
    {
        return User::where('email', $request->get('email'))->where('password', $request->get('password'))->first();
    }

    protected function getUserById($userId)
    {
        return User::find($userId);
    }

    protected function getTokenPayload($user): array
    {
        return [
            'userId' => $user->id,
            'name' => $user->name
        ];
    }

    protected function getResponseAdditionalPayload($user): array
    {
        return [
            'name' => $user->name
        ];
    }
}
