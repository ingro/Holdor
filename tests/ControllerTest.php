<?php


namespace Ingruz\Holdor\Test;

use Ingruz\Holdor\Helpers\JWTHelper;

class ControllerTest extends TestCase
{
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
    public function should_return_the_protected_response_if_the_token_is_valid()
    {
        $helper = new JWTHelper();

        $token = $helper->issue([
            'userId' => 2
        ]);

        $response = $this->json('GET', '/protected?token=' . $token);

        $response->assertSuccessful();
        
        $json = $response->decodeResponseJson();

        $this->assertEquals('Jon Doe', $json['name']);
    }
}
