<?php
namespace Autobahn\Cli\Commands\Keys;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateCommand
 * Generate new WordPress keys and salts.
 * @package Autobahn\Cli\Commands\Keys
 */
class GenerateCommand extends Command
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
                'secure',
                's',
                InputOption::VALUE_NONE,
                'Ask before overriding existing keys and salts'
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
        // get options
        $secure = $input->getOption('secure');
        $export = $input->getOption('export');

        // add every key
        foreach ($this->keys as $key) {
            if ($output->isVerbose()) {
                $output->writeln($this->composeLine($key, $this->randomKey(64), $export));
            }
        }
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
