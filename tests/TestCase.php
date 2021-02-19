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
        $app['config']->set('app.debug', true);

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('holdor.token_expire', 3600);
        $app['config']->set('holdor.token_secret', 'abcd');
        $app['config']->set('holdor.refresh_expire', 7200);
    }

    // protected function resolveApplicationExceptionHandler($app)
    // {
    //     $app->singleton('Illuminate\Contracts\Debug\ExceptionHandler', 'Orchestra\Testbench\Exceptions\Handler');
    // }

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
        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });

        $user = new User();

        $user->name = 'Foo Bar';
        $user->email = 'foo@example.com';
        $user->password = 'password';

        $user->save();

        $user = new User();

        $user->name = 'Jon Doe';
        $user->email = 'jon@example.com';
        $user->password = 'password';

        $user->save();
    }
}
