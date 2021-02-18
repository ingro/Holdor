<?php


namespace Ingruz\Holdor\Test;

use App\Http\Controllers\TestController;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
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

        $this->app->make('router')->aliasMiddleware('holdor', HoldorMiddleware::class);

        $this->setUpRoutes();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', true);
    }

    protected function resolveApplicationExceptionHandler($app)
    {
        $app->singleton('Illuminate\Contracts\Debug\ExceptionHandler', 'Orchestra\Testbench\Exceptions\Handler');
    }

    protected function setUpRoutes()
    {
        Route::group(['middleware' => 'holdor'], function() {
            Route::get('/protected', [TestController::class, 'getSecret']);
        });
    }
}
