<?php


namespace Ingruz\Holdor\Test;


use Ingruz\Holdor\Helpers\JWTHelper;

class HoldorControllerTest extends TestCase
{
    /** @test */
    public function should_return_an_error_if_the_credentials_are_invalid()
    {
        $response = $this->json('POST', '/auth', [
            'email' => 'foo@example.com',
            'password' => 'foobar'
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function should_be_able_to_login_and_obtain_a_valid_token()
    {
        $response = $this->json('POST', '/auth', [
            'email' => 'foo@example.com',
            'password' => 'password'
        ]);

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'token',
            'refresh_token',
            'expire',
            'name'
        ]);

        $json = $response->decodeResponseJson();

        $this->assertEquals('Foo Bar', $json['name']);

        $responseWithToken = $this->json('GET', '/protected?token=' . $json['token']);

        $responseWithToken->assertSuccessful();

        $helper = new JWTHelper();

        $parsedToken = $helper->verify($json['token']);

        $this->assertEquals(1, $parsedToken->getClaim('userId'));
    }

    /** @test */
    public function should_be_able_to_obtain_a_new_valid_token_from_refresh()
    {
        $this->travel(-30)->minutes();

        $response = $this->json('POST', '/auth', [
            'email' => 'foo@example.com',
            'password' => 'password'
        ]);

        $json = $response->decodeResponseJson();

        $this->travelBack();

        $refreshResponse = $this->json('POST', '/refresh?token=' . $json['refresh_token']);

        $refreshResponse->assertSuccessful();

        $refreshResponse->assertJsonStructure([
            'token',
            'refresh_token',
            'expire',
            'name'
        ]);

        $jsonRefresh = $response->decodeResponseJson();

        $responseWithToken = $this->json('GET', '/protected?token=' . $jsonRefresh['token']);

        $responseWithToken->assertSuccessful();
    }

    /** @test */
    public function should_return_an_error_if_the_refresh_token_is_expired()
    {
        $this->travel(-6)->hours();

        $response = $this->json('POST', '/auth', [
            'email' => 'foo@example.com',
            'password' => 'password'
        ]);

        $json = $response->decodeResponseJson();

        $this->travelBack();

        $refreshResponse = $this->json('POST', '/refresh?token=' . $json['refresh_token']);

        $refreshResponse->assertStatus(401);
    }

    /** @test */
    public function should_return_an_error_if_the_token_to_refresh_is_not_a_refresh_token()
    {
        $this->travel(-30)->minutes();

        $response = $this->json('POST', '/auth', [
            'email' => 'foo@example.com',
            'password' => 'password'
        ]);

        $json = $response->decodeResponseJson();

        $this->travelBack();

        $refreshResponse = $this->json('POST', '/refresh?token=' . $json['token']);

        $refreshResponse->assertStatus(401);
    }
}
