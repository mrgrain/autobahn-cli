<?php
namespace Autobahn\Cli\Commands\Env;

use Autobahn\Cli\Utils\Dotenv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

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
}
