<?php
namespace Taskle\Generate;

use Silex\Application;
use Cilex\Provider\Console\ConsoleServiceProvider;

class Bootstrap
{
    public static function configure(Application $app)
    {
        $app['console.name'] = 'TaskleGenerate';
        $app['console.version'] = '2.0.0';

        $consoleServiceProvider = new ConsoleServiceProvider;
        $consoleServiceProvider->register($app);
    }
}
