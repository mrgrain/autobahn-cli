<?php
namespace Autobahn\Cli\Commands\Keys;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DisplayCommand
 * Display current WordPress keys and salts.
 * @package Autobahn\Cli\Commands\Keys
 */
class DisplayCommand extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('keys:display')
            ->setDescription('Display current WordPress keys and salts');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {


        /*
         * AUTH_KEY=
SECURE_AUTH_KEY=
LOGGED_IN_KEY=
NONCE_KEY=
AUTH_SALT=
SECURE_AUTH_SALT=
LOGGED_IN_SALT=
NONCE_SALT=

         */
    }
}
