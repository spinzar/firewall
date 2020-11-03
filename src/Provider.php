<?php

namespace Spinzar\Firewall;

use Spinzar\Firewall\Commands\UnblockIp;
use Spinzar\Firewall\Events\AttackDetected;
use Spinzar\Firewall\Listeners\BlockIp;
use Spinzar\Firewall\Listeners\CheckLogin;
use Spinzar\Firewall\Listeners\NotifyUsers;
use Illuminate\Auth\Events\Failed as LoginFailed;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class Provider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Router $router
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->publishes([
            __DIR__ . '/Config/firewall.php'                                            => config_path('firewall.php'),
            __DIR__ . '/Migrations/2020_11_01_000000_create_firewall_ips_table.php'     => database_path('migrations/2020_11_01_000000_create_firewall_ips_table.php'),
            __DIR__ . '/Migrations/2020_11_01_000000_create_firewall_logs_table.php'    => database_path('migrations/2020_11_01_000000_create_firewall_logs_table.php'),
            __DIR__ . '/Resources/lang'                                                 => resource_path('lang/vendor/firewall'),
        ], 'firewall');

        $this->registerMiddleware($router);
        $this->registerListeners();
        $this->registerTranslations();
        $this->registerCommands();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/firewall.php', 'firewall');

        $this->app->register(\Jenssegers\Agent\AgentServiceProvider::class);
    }

    /**
     * Register middleware.
     *
     * @param Router $router
     *
     * @return void
     */
    public function registerMiddleware($router)
    {
        $router->middlewareGroup('firewall.all', config('firewall.all_middleware'));
        $router->aliasMiddleware('firewall.agent', 'Spinzar\Firewall\Middleware\Agent');
        $router->aliasMiddleware('firewall.bot', 'Spinzar\Firewall\Middleware\Bot');
        $router->aliasMiddleware('firewall.ip', 'Spinzar\Firewall\Middleware\Ip');
        $router->aliasMiddleware('firewall.geo', 'Spinzar\Firewall\Middleware\Geo');
        $router->aliasMiddleware('firewall.lfi', 'Spinzar\Firewall\Middleware\Lfi');
        $router->aliasMiddleware('firewall.php', 'Spinzar\Firewall\Middleware\Php');
        $router->aliasMiddleware('firewall.referrer', 'Spinzar\Firewall\Middleware\Referrer');
        $router->aliasMiddleware('firewall.rfi', 'Spinzar\Firewall\Middleware\Rfi');
        $router->aliasMiddleware('firewall.session', 'Spinzar\Firewall\Middleware\Session');
        $router->aliasMiddleware('firewall.sqli', 'Spinzar\Firewall\Middleware\Sqli');
        $router->aliasMiddleware('firewall.swear', 'Spinzar\Firewall\Middleware\Swear');
        $router->aliasMiddleware('firewall.url', 'Spinzar\Firewall\Middleware\Url');
        $router->aliasMiddleware('firewall.whitelist', 'Spinzar\Firewall\Middleware\Whitelist');
        $router->aliasMiddleware('firewall.xss', 'Spinzar\Firewall\Middleware\Xss');
    }

    /**
     * Register listeners.
     *
     * @return void
     */
    public function registerListeners()
    {
        $this->app['events']->listen(AttackDetected::class, BlockIp::class);
        $this->app['events']->listen(AttackDetected::class, NotifyUsers::class);
        $this->app['events']->listen(LoginFailed::class, CheckLogin::class);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $lang_path = resource_path('lang/vendor/firewall');

        if (is_dir($lang_path)) {
            $this->loadTranslationsFrom($lang_path, 'firewall');
        } else {
            $this->loadTranslationsFrom(__DIR__ . '/Resources/lang', 'firewall');
        }
    }

    public function registerCommands()
    {
        $this->commands(UnblockIp::class);

        $this->app->booted(function () {
            app(Schedule::class)->command('firewall:unblockip')->everyMinute();
        });
    }
}
