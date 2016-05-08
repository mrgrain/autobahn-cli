<?php
namespace Autobahn\Cli\Commands\Keys;

use Autobahn\Cli\Commands\Env\EnvCommand;
use Autobahn\Cli\Contracts\Dotenv;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class GenerateCommand
 * Generate new WordPress keys and salts.
 * @package Autobahn\Cli\Commands\Keys
 */
class GenerateCommand extends EnvCommand
{
    /**
     * List of required WordPress keys and salts
     *
     * @var array
     */
    protected $keys = [
        'AUTH_KEY',
        'SECURE_AUTH_KEY',
        'LOGGED_IN_KEY',
        'NONCE_KEY',
        'AUTH_SALT',
        'SECURE_AUTH_SALT',
        'LOGGED_IN_SALT',
        'NONCE_SALT',
    ];

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('keys:generate')
            ->setDescription('Generate new WordPress keys and salts')
            ->addOption(
                'override',
                'o',
                InputOption::VALUE_NONE,
                'Overriding existing keys without asking'
            )
            ->addOption(
                'export',
                null,
                InputOption::VALUE_NONE,
                'Prefix lines with <code>export</code> so you can source the file in bash'
            );
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // retrieve arguments
        $export = $input->getOption('export');
        $override = $input->getOption('override');

        // questions
        $helper = $this->getHelper('question');
        $override_question = (new ConfirmationQuestion(
            "<question>WordPress keys already exists. Override?</question> [y/N]",
            false
        ));

        // prepare dotenv access
        $dotenv = $this->getDotenv($this->getFilePath($input));

        // abort if not overriding
        if (!$override && $this->anyKeyExists($dotenv) && !$helper->ask($input, $output, $override_question)) {
            $output->writeln('<error>Aborting</error>');
            return 1;
        }

        // generate and add all keys
        $data = [];
        foreach ($this->keys as $key) {
            $dotenv->set($key, $this->randomKey(64), $export);
            $data[$key] = $dotenv->get($key);
        }

        // list added keys
        if ($output->isVerbose()) {
            $this->formatVariables($output, $data, "WordPress Key")->render();
        }

        // confirm writing
        if (!$output->isQuiet()) {
            $output->writeln('<info>WordPress keys successfully written to file.</info>');
        }
        return 0;
    }

    /**
     * Determine if any WordPres key already exists in the dotenv file
     * @param Dotenv $dotenv
     * @return bool
     */
    protected function anyKeyExists(Dotenv $dotenv)
    {
        foreach ($this->keys as $key) {
            if ($dotenv->has($key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Create a random key with out slashes and quotes
     * @param $length
     * @return string
     */
    protected function randomKey($length)
    {
        $string = '';
        while (($len = mb_strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= mb_substr(str_replace(['\\', '/', '"', '\''], '', base64_encode($bytes)), 0, $size, 'UTF-8');
        }
        return $string;
    }
}
