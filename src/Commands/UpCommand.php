<?php
namespace Autobahn\Cli\Commands;

use Dotenv\Dotenv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Tivie\OS\Detector;

/**
 * Class UpCommand
 * Start the vagrant box.
 * @package Autobahn\Cli\Commands
 */
class UpCommand extends Command
{
    /**
     * @var Detector
     */
    protected $os;

    /**
     * Default file name for the dotenv file.
     *
     * @var string
     */
    protected $fileName = ".env";

    /**
     * UpCommand constructor.
     * @param null|string $name
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->os = new Detector();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('up')
            ->setDescription('Starts and provisions the Autobahn vagrant environment');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Check if vagrant is available
        if (!$this->isVagrantInstalled()) {
            $output->writeln('<error>Couldn\'t find `vagrant` in PATH. Are you sure Vagrant is installed?</error>');
            return 1;
        }

        // load .env
        $this->getDotenv(getcwd())->load();

        // run vagrant
        putenv("WP_HOME={$this->getWordPressHome()}");
        putenv("VAGRANT_HOSTNAME={$this->getHostname()}");
        $vagrant = new Process('vagrant up');
        $vagrant->run(function ($type, $buffer) use ($output) {
            if (Process::ERR === $type) {
                $buffer = "<error>$buffer</error>";
            }
            return $output->write($buffer, false, OutputInterface::VERBOSITY_VERBOSE);
        });

        // vagrant failed
        if (!$vagrant->isSuccessful()) {
            return $vagrant->getExitCode();
        }

        // start browser
        $browser = new Process($this->getBrowserCommand($this->getWordPressHome()));
        $browser->run();

        return 0;
    }

    /**
     * Test if vagrant is installed or not.
     * @return boolean
     */
    protected function isVagrantInstalled()
    {
        $process = new Process('vagrant -v');
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * Get the value of the WP_HOME constant
     * @return string
     */
    protected function getWordPressHome()
    {
        return getenv('WP_HOME') ?: 'http://my.autobahn.rocks';
    }

    /**
     * Get the hostname for the WordPress install
     * @return string
     */
    protected function getHostname()
    {
        return parse_url($this->getWordPressHome(), PHP_URL_HOST);
    }

    /**
     * Find the right command to launch a url on a platform.
     * @param string $url
     * @return string
     */
    protected function getBrowserCommand($url)
    {
        // Mac
        if (\Tivie\OS\MACOSX == $this->os->getType()) {
            return "open $url";
        }

        // Windows
        if ($this->os->isWindowsLike()) {
            return "start $url";
        }

        // everything else (most likely unix)
        return "xdg-open $url";
    }

    /**
     * @param $path
     * @return Dotenv
     */
    protected function getDotenv($path)
    {
        return new Dotenv($path, $this->fileName);
    }
}
