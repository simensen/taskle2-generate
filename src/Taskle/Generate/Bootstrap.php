<?php
namespace Taskle\Generate;

use Cilex\Provider\Console\ConsoleServiceProvider;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\Console\Command\Command;
use Taskle\Generator\Pimple\PimpleAwareInterface;

class Bootstrap
{
    public static function configure(Application $app)
    {
        $app['console.name'] = 'Taskle Generate';
        $app['console.version'] = '2.0.0';

        $consoleServiceProvider = new ConsoleServiceProvider;
        $consoleServiceProvider->register($app);

        $app->register(new TwigServiceProvider());
        $app['twig'] = $app->extend('twig', function ($twig, $app) {
            // $twig->addExtension(new BootstrapFormExtension());
            return $twig;
        });

        $commands = array(
            new Command\CreateAction(),
        );

        foreach ($commands as $command) {
            if ($command instanceof PimpleAwareInterface) {
                $command->setContainer($app);
            }
            if ($command instanceof Command) {
                $app['console']->add($command);
            }
        }
    }
}
