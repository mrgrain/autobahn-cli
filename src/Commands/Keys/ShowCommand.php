<?php
namespace Autobahn\Cli\Commands\Keys;

use Autobahn\Cli\Commands\Env\EnvCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DisplayCommand
 * Display current WordPress keys and salts.
 * @package Autobahn\Cli\Commands\Keys
 */
class ShowCommand extends EnvCommand
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
            ->setName('keys:show')
            ->setDescription('Show current WordPress keys and salts');
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // prepare dotenv access
        $dotenv = $this->getDotenv($this->getFilePath($input));

        // Display table of keys
        $data = [];
        foreach ($this->keys as $key) {
            if ($dotenv->has($key)) {
                $data[$key] = $dotenv->get($key);
            }
        }
        $this->formatVariables($output, $data, "WordPress Key")->render();
        return 0;
    }
}
