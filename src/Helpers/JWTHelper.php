<?php


namespace Ingruz\Holdor\Helpers;


use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;

class JWTHelper
{
    /**
     * @param array $params
     * @param int $expire
     * @param int $after
     * @return Token
     */
    public function issue($params = [], $expire = null, $after = 0)
    {
        $signer = new Sha256();
        $time = time();

        if ($expire === null) {
            $expire = config('holdor.token_expire');
        }

        $token = (new Builder())
            // ->issuedBy('http://example.com') // Configures the issuer (iss claim)
            // ->permittedFor('http://example.org') // Configures the audience (aud claim)
            ->identifiedBy(uniqid(), true) // Configures the id (jti claim), replicating as a header item
            ->issuedAt($time) // Configures the time that the token was issue (iat claim)
            ->canOnlyBeUsedAfter($time + $after) // Configures the time that the token can be used (nbf claim)
            ->expiresAt($time + $expire); // Configures the expiration time of the token (exp claim)

        foreach ($params as $key => $value) {
            $token->withClaim($key, $value);
        }

        return $token->getToken($signer, new Key(config('holdor.token_secret')));
    }

    /**
     * @param $tokenString
     * @return bool|Token
     */
    public function verify($tokenString)
    {
        $signer = new Sha256();

        try {
            $token = (new Parser())->parse((string) $tokenString);

            if (! $token->verify($signer, new Key(config('holdor.token_secret')))) {
                return false;
            }

            $validationData = new ValidationData();

            if (! $token->validate($validationData)) {
                return false;
            }

            return $token;
        } catch (\Exception $e) {
            return false;
        }
    }
}