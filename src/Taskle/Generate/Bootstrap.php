<?php
namespace Taskle\Generate;

use Cilex\Provider\Console\ConsoleServiceProvider;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\Console\Command\Command;
use Taskle\Generate\Command\Build;
use Taskle\Generate\Pimple\PimpleAwareInterface;
use Taskle\Generate\Twig\CaseExtension;
use Taskle\Generate\Twig\ParameterizeExtension;

class Bootstrap
{
    public static function configure(Application $app)
    {
        $app['console.name'] = 'Taskle Generate';
        $app['console.version'] = '2.0.0';

        $consoleServiceProvider = new ConsoleServiceProvider;
        $consoleServiceProvider->register($app);

        $app->register(new TwigServiceProvider(), array(
            'twig.path' => __DIR__ . '/Templates',
        ));
        $app['twig'] = $app->extend('twig', function ($twig, $app) {
            $twig->addExtension(new CaseExtension());
            $twig->addExtension(new ParameterizeExtension());
            return $twig;
        });

        $commands = array(
            new Build(),
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
