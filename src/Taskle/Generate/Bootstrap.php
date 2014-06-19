<?php
namespace Taskle\Generate;

use Cilex\Provider\Console\ConsoleServiceProvider;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;

class Bootstrap
{
    public static function configure(Application $app)
    {
        $app['console.name'] = 'TaskleGenerate';
        $app['console.version'] = '2.0.0';

        $consoleServiceProvider = new ConsoleServiceProvider;
        $consoleServiceProvider->register($app);

        $app->register(new TwigServiceProvider());
        $app['twig'] = $app->extend('twig', function ($twig, $app) {
            // $twig->addExtension(new BootstrapFormExtension());
            return $twig;
        });
    }
}
