<?php

namespace Carew\Plugin\Sami\Command;

use Carew\Plugin\Sami\SamiConfiguration;
use Sami\Console\Command\UpdateCommand as BaseUpdateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends BaseUpdateCommand
{
    private $config;

    public function __construct(array $config = array())
    {
        $this->config = $config;

        parent::__construct();
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setDefinition(array(
                new InputOption('--force', '', InputOption::VALUE_NONE, 'Forces to rebuild from scratch', null),
            ))
            ->setName('sami:update')
            ->setDescription('Generate doc for a project')
            ->setHelp('')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;

        if (!isset($this->config['sami']) || !is_array($this->config['sami'])) {
            throw new \InvalidArgumentException(sprintf('You should set up a sami configuration in "%s/config.yml"', $input->getOption('base-dir')));
        }

        $config = $this->config['sami'];
        foreach (array('project_dir', 'src_dir', 'name', 'branches') as $key) {
            if (!isset($config[$key])) {
                throw new \InvalidArgumentException(sprintf('You should set up the "%s" key in "%s/config.yml"', $key, $input->getOption('base-dir')));
            }
        }

        if (!is_array($config['branches']) || empty($config['branches']) ) {
            throw new \InvalidArgumentException(sprintf('The "branches" key should be an array in "%s/config.yml"', $input->getOption('base-dir')));
        }

        if (!is_dir($config['project_dir'].'/'.$config['src_dir'])) {
            throw new \InvalidArgumentException(sprintf(
                'The directory "%s" doest not exists. Check your config.yml',
                $config['src_dir']
            ));
        }

        $samiConfiguration = new SamiConfiguration($config['project_dir'], $config['src_dir'], $config['name'], $config['branches'], $input->getOption('base-dir').'/api');
        $this->sami = $samiConfiguration->getConfiguration();
    }
}
