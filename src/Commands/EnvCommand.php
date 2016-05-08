<?php
namespace Autobahn\Cli\Commands;

use Autobahn\Cli\Utils\Dotenv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

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
                'The variable to set'
            )
            ->addOption(
                'value',
                null,
                InputOption::VALUE_REQUIRED,
                'Value of the variable'
            )
            ->addOption(
                'secure',
                's',
                InputOption::VALUE_NONE,
                'Ask before overriding existing variables'
            )
            ->addOption(
                'export',
                null,
                InputOption::VALUE_NONE,
                'Prefix lines with <code>export</code> so you can source the file in bash'
            )
            ->addOption(
                'file',
                null,
                InputOption::VALUE_REQUIRED,
                'Filepath of the dotenv file. Defaults to "./.env".'
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
        $secure = $input->getOption('secure');
        $file = $input->getOption('file') ?: getcwd() . DIRECTORY_SEPARATOR . '.env';

        // questions
        $helper = $this->getHelper('question');
        $override_question = (new ConfirmationQuestion(
            "<question>Environment variable <code>$name</code> already exists. Override?</question> (yes/NO)" . PHP_EOL,
            false
        ));

        // prepare dotenv access
        $dotenv = new Dotenv($file);

        // abort if overriding
        if ($secure && $dotenv->has($name) && !$helper->ask($input, $output, $override_question)) {
            $output->writeln('<error>Aborting.</error>');
            return 1;
        }

        // write new value
        $result = $dotenv->set($name, $value, (bool)$export);
        if ($output->isVerbose()) {
            $output->writeln($result);
        }

        if (!$output->isQuiet()) {
            $output->writeln('<info>Variable successfully written to file.</info>');
        }
        return 0;
    }
}
