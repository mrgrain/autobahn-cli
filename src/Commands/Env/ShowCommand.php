<?php
namespace Autobahn\Cli\Commands\Env;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class EnvCommand
 * Set values to a dotenv file.
 * @package Autobahn\Cli\Commands
 */
class ShowCommand extends EnvCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('env:show')
            ->setDescription('Show an environmental variable from the dotenv file')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The variable to set'
            )
            ->addOption(
                'file',
                null,
                InputOption::VALUE_REQUIRED,
                'Filepath of the dotenv file. Defaults to "./.env".'
            );
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // retrieve arguments
        $name = $input->getArgument('name');

        // prepare dotenv access
        $dotenv = $this->getDotenv($this->getFilePath($input));

        // display variable
        if ($dotenv->has($name)) {
            $this->formatVariables($output, [$name => $dotenv->get($name)]);
        }

        return 0;
    }
}
