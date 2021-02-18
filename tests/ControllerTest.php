<?php


namespace Ingruz\Holdor\Test;


class ControllerTest extends TestCase
{
    public function testProtectedRoute()
    {
        $response = $this->json('GET', '/protected');

        $response->assertStatus(401);
    }
}
