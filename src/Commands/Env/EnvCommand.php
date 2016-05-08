<?php
namespace Autobahn\Cli\Commands\Env;

use Autobahn\Cli\Utils\Dotenv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class EnvCommand
 * Set values to a dotenv file.
 * @package Autobahn\Cli\Commands
 */
abstract class EnvCommand extends Command
{
    /**
     * Default file name for the dotenv file.
     *
     * @var string
     */
    protected $fileName = ".env";

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->addOption(
                'file',
                null,
                InputOption::VALUE_REQUIRED,
                'Filepath of the dotenv file. Defaults to "./.env".'
            );
    }

    /**
     * Get the file path from input or fallback to default.
     * @param InputInterface $input
     * @return string
     */
    protected function getFilePath(InputInterface $input)
    {
        return $input->getOption('file') ?: getcwd() . DIRECTORY_SEPARATOR . $this->fileName;
    }

    /**
     * @param $file
     * @return Dotenv
     */
    protected function getDotenv($file)
    {
        return new Dotenv($file);
    }

    /**
     * @param OutputInterface $output
     * @param array $data
     * @param string $column1
     * @param string $column2
     * @return Table
     */
    protected function formatVariables(OutputInterface $output, array $data, $column1 = 'Environment Variable', $column2 = "Value")
    {
        $table = new Table($output);
        $table
            ->setHeaders([$column1, $column2]);

        foreach ($data as $name => $value) {
            $table->addRow([$name, $value]);
        }
        return $table;
    }
}
