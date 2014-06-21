<?php
namespace Taskle\Generate\Command;

use Pimple\Container;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Taskle\Generate\Pimple\PimpleAwareInterface;

/**
 * Abstract command, contains bootstrapping info
 *
 * @author Rob Morgan <robbym@gmail.com>
 */
abstract class AbstractCommand extends Command implements PimpleAwareInterface
{
    protected $config;
    protected $container;
    protected $cwd;

    protected function configure()
    {
        $this->addOption('--configuration', '-c', InputArgument::OPTIONAL, 'The configuration file to load');
    }

    public function bootstrap(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->container['console']->getLongVersion());

        if (!$this->getConfig()) {
            $this->loadConfig($input, $output);
        }

        $this->getHelperSet()->set(new DialogHelper());
        $this->getHelperSet()->set(new FormatterHelper());
    }

    protected function writeSection(OutputInterface $output, $text, $style = 'bg=blue;fg=white')
    {
        $output->writeln(array(
            '',
            $this->getHelperSet()->get('formatter')->formatBlock($text, $style, true),
            '',
        ));
    }

    protected function getQuestion($question, $default, $sep = ':')
    {
        return $default ? sprintf('<info>%s</info> [<comment>%s</comment>]%s ', $question, $default, $sep) : sprintf('<info>%s</info>%s ', $question, $sep);
    }

    protected function render($view, array $parameters = array())
    {
        $this->container['twig']->render($view, $parameters);
    }

    public function setContainer(Container $container = null)
    {
        $this->container = $container;
    }

    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    protected function locateConfigFile(InputInterface $input)
    {
        $configFile = $input->getOption('configuration');

        if (null === $configFile) {
            $configFile = 'generator.yml';
        }

        $this->cwd = getcwd();

        $locator = new FileLocator(array(
            $this->cwd . DIRECTORY_SEPARATOR
        ));

        // Locate() throws an exception if the file does not exist
        return $locator->locate($configFile, $this->cwd, $first = true);
    }

    protected function loadConfig(InputInterface $input, OutputInterface $output)
    {
        $configFilePath = $this->locateConfigFile($input);
        $output->writeln('<info>using config file</info> .' . str_replace(getcwd(), '', realpath($configFilePath)));

        $config = Yaml::parse($configFilePath);

        $this->setConfig($config);
    }
}
