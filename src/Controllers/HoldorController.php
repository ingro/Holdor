<?php

namespace Ingruz\Holdor\Controllers\HoldorController;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Ingruz\Holdor\Helpers\JWTHelper;

abstract class HoldorController extends Controller {

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var JWTHelper
     */
    private $JWTHelper;

    public function __construct(JWTHelper $JWTHelper)
    {
        $this->JWTHelper = $JWTHelper;
    }

    public function issueToken(Request $request) 
    {
        $user = $this->getUserByRequest($request);

        if (! $user) {
            return response()->json(['error' => 'Invalid credentials'], 400);
        }

        $payload = $this->generateResponsePayload($user);

        return \Response::json($payload);
    }

    public function refreshToken(Request $request)
    {
        if (! $request->hasHeader('authorization') and ! $request->input('token')) {
            throw new JWTMismatchException('Please provide an Auth Token!');
        }

        $tokenString = $request->hasHeader('authorization') ? str_replace('Bearer ', '', $request->header('authorization')) : $request->input('token');

        $token = $this->JWTHelper->verify($tokenString);

        if (!$token || !$token->hasClaim('type') || $token->getClaim('type') !== 'refresh') {
            throw new JWTMismatchException('You should provide a Refresh Token!');
        }

        $user = $this->getUserById($token->getClaim('userId'));

        $payload = $this->generateResponsePayload($user);

        return \Response::json($payload);
    }

    protected function generateResponsePayload($user) {
        $tokenPayload = $this->getTokenPayload($user);

        $token = $this->JWTHelper->issue($tokenPayload);

        $refreshTokenPayload = array_merge($tokenPayload, ['type' => 'refresh']);

        $refreshToken = $this->JWTHelper->issue(refreshTokenPayload, config('holdor.refresh_expire'), 0);

        $additionalPayload = $this->getResponseAdditionalPayload($user);

        return array_merge($additionalPayload, [
            'token' => (string) $token,
            'refresh_token' => (string) $refreshToken,
            'expire' => $token->getClaim('exp')
        ]);
    }

    abstract protected function getUserByRequest(Request $request);

    abstract protected function getUserById($userId);

    abstract protected function getTokenPayload($user);

    abstract protected function getResponseAdditionalPayload($user);
}
