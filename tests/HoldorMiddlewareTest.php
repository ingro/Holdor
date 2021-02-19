<?php


namespace Ingruz\Holdor\Test;

use Ingruz\Holdor\Helpers\JWTHelper;

class HoldorMiddlewareTest extends TestCase
{
    /**
     * @var JWTHelper
     */
    protected $helper;

    public function setUp(): void
    {
        parent::setUp();

        $this->helper = new JWTHelper();
    }

    /** @test */
    public function should_return_auth_error_if_no_token_is_provided()
    {
        $response = $this->json('GET', '/protected');

        $response->assertStatus(401);
    }

    /** @test */
    public function should_return_auth_error_if_the_token_is_invalid()
    {
        $response = $this->json('GET', '/protected?token=foobar');

        $response->assertStatus(401);
    }

    /** @test */
    public function should_return_auth_error_if_the_token_is_expired()
    {
        $this->travel(-10)->hours();

        $token = $this->helper->issue([
            'userId' => 2
        ]);

        $response = $this->json('GET', '/protected?token=' . $token);

        $response->assertStatus(401);
    }

    /** @test */
    public function should_return_the_protected_response_if_the_token_is_valid()
    {
        $token = $this->helper->issue([
            'userId' => 2
        ]);

        $response = $this->json('GET', '/protected?token=' . $token);

        $response->assertSuccessful();

        $json = $response->decodeResponseJson();

        $this->assertEquals('Jon Doe', $json['name']);

        $responseFromAuthHeader = $this->json('GET', '/protected', [], [
            'authorization' => 'Bearer ' . $token
        ]);

        $responseFromAuthHeader->assertSuccessful();
    }
}
