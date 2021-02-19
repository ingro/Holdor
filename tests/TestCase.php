<?php


namespace Ingruz\Holdor\Test;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestController;
use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Schema\Blueprint;
use Ingruz\Holdor\Middleware\HoldorMiddleware;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Set up the test env
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->setupHoldor($this->app);

        $this->setUpDatabase($this->app);

        $this->setUpRoutes();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app->config->set('app.debug', true);

        $app->config->set('database.default', 'testbench');
        $app->config->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app->config->set('holdor.token_expire', 3600);
        $app->config->set('holdor.token_secret', 'abc');
        $app->config->set('holdor.refresh_expire', 7200);
    }

    protected function setupHoldor($app)
    {
        $app->make('router')->aliasMiddleware('holdor', HoldorMiddleware::class);
    }

    protected function setUpRoutes()
    {
        Route::group(['middleware' => 'holdor'], function() {
            Route::get('/protected', [TestController::class, 'getSecret']);
        });

        Route::post('/auth', [AuthController::class, 'issueToken']);
        Route::post('/refresh', [AuthController::class, 'refreshToken']);
    }

    protected function setUpDatabase($app)
    {
        $this->loadLaravelMigrations(['--database' => 'testbench']);

        User::create([
            'name' => 'Foo Bar',
            'email' => 'foo@example.com',
            'password' => 'password'
        ]);

        User::create([
            'name' => 'Jon Doe',
            'email' => 'jon@example.com',
            'password' => 'password'
        ]);
    }
}
