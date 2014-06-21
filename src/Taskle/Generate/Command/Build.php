<?php
namespace Taskle\Generate\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Build extends AbstractCommand
{
    public static function configure(Application $app)
    {
        parent::configure();

        $this->setName('build')
             ->setDescription('Build objects from config definition')
             ->setHelp("The <info>build</info> command creates new domain objects from generator.yml config file.\n\n<info>generate build</info>\n");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input, $output);

        $config = $this->getConfig();

        $output->writeln(print_r($this->config,true));
    }
}
