<?php
namespace Taskle\Generate\Command;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Taskle\Generate\Renderer;

class Domain extends AbstractCommand
{
    public function configure()
    {
        parent::configure();

        $this->setName('domain')
             ->setDescription('Generate domain objects from config definition')
             ->setHelp("The <info>domain</info> command creates new domain objects from generator.yml config file.\n\n<info>generate domain</info>\n");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input, $output);

        $config = $this->getConfig();

        $sourceDir = realpath($this->cwd . $config['source']);
        $testsDir = realpath($this->cwd . $config['tests']);

        // $output->writeln(print_r($this->config,true));

        if (isset($config['models']) && is_array($config['models'])) {
            $renderer = new Renderer\Model($this->container['twig'], $sourceDir, $testsDir);
            foreach ($config['models'] as $singular => $model) {
                if (!isset($model['singular'])) {
                    $model['singular'] = $singular;
                }
                try {
                    $renderer->build($model);
                } catch (Exception $e) {
                    $output->writeln("<info>Exception</info> " . $e->getMessage());
                }
            }
        }
    }
}
