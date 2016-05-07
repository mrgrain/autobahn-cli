<?php
namespace Autobahn\Cli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class EnvCommand
 * Set values to a dotenv file.
 * @package Autobahn\Cli\Commands
 */
class EnvCommand extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('env')
            ->setDescription('Set an environmental variable in the .env file')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Which variable do you want to set?'
            )
            ->addOption(
                'value',
                null,
                InputOption::VALUE_REQUIRED,
                'What value do you want to set it to?'
            )
            ->addOption(
                'export',
                null,
                InputOption::VALUE_NONE,
                'Prefix lines with <code>export</code> so you can <code>source</code> the file in bash'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // retrieve arguments
        $name = $input->getArgument('name');
        $value = $input->getOption('value');
        $export = $input->getOption('export');

        $output->writeln($this->composeLine($name, $value, $export));
    }

    /**
     * Compose the dotenv line from given input
     * @param $name
     * @param $value
     * @param bool $export
     * @return string
     */
    protected function composeLine($name, $value, $export = false)
    {
        return sprintf('%s%s="%s"', ($export ? 'export ' : ''), $name, addcslashes($value, '"\\'));
    }
}
