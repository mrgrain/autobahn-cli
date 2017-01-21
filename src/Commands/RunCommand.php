<?php
namespace Autobahn\Cli\Commands;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Tivie\OS\Detector;
use UnexpectedValueException;

/**
 * Class RunCommand
 * Start the vagrant box.
 *
 * @package Autobahn\Cli\Commands
 */
class RunCommand extends Command
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
     * Default file name for the dotenv template.
     *
     * @var string
     */
    protected $templateName = ".env.example";

    /**
     * UpCommand constructor.
     *
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
            ->setName('run')
            ->setDescription('Starts and provisions the Autobahn vagrant environment')
            ->addOption(
                'copy-env-from',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to an .env file to copy if one doesn\'t exist in the current directory.'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // Check if vagrant is available
        if (!$this->isVagrantInstalled()) {
            $io->error('Couldn\'t find `vagrant` in PATH. Are you sure Vagrant is installed?');
            return 1;
        }
        if (!$this->hasVagrantfile()) {
            $io->error('Couldn\'t find local `Vagrantfile`. A vagrant environment is required to run this command.'
                       . ' Run `vagrant init` to create a new Vagrant environment.'
                       . ' Or change to a directory with a Vagrantfile and to try again.');
            return 1;
        }

        // create .env
        if (!file_exists($this->fileName) && $envTemplate = $this->getEnvTemplate($input)) {
            $io->note("Creating .env from template {$envTemplate}.");
            try {
                $this->createEnv($envTemplate);
            } catch (UnexpectedValueException $exception) {
                $io->warning("{$exception->getMessage()} Skipping.");
            }
        }

        // load .env
        try {
            $this->getDotenv(getcwd())->load();
        } catch (InvalidPathException $exception) {
            $io->note("No .env file found. Falling back to environment.");
        }

        // run vagrant
        putenv("WP_HOME={$this->getWordPressHome()}");
        putenv("VAGRANT_HOSTNAME={$this->getHostname()}");
        $vagrant = new Process('vagrant up', null, null, null, null);
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
     *
     * @return boolean
     */
    protected function isVagrantInstalled()
    {
        $process = new Process('vagrant -v');
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * Test if a Vagrantfile is present.
     *
     * @return boolean
     */
    protected function hasVagrantfile()
    {
        return file_exists(getcwd() . DIRECTORY_SEPARATOR . 'Vagrantfile');
    }

    /**
     * Get the value of the WP_HOME constant
     *
     * @return string
     */
    protected function getWordPressHome()
    {
        return getenv('WP_HOME') ?: 'http://my.autobahn.rocks';
    }

    /**
     * Get the hostname for the WordPress install
     *
     * @return string
     */
    protected function getHostname()
    {
        return parse_url($this->getWordPressHome(), PHP_URL_HOST);
    }

    /**
     * Find the right command to launch a url on a platform.
     *
     * @param string $url
     *
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
     *
     * @return Dotenv
     */
    protected function getDotenv($path)
    {
        return new Dotenv($path, $this->fileName);
    }

    /**
     * Get the path to copy the .env template from.
     *
     * @param InputInterface $input
     *
     * @return false|string
     */
    protected function getEnvTemplate(InputInterface $input)
    {
        return $input->getOption('copy-env-from') ?: getcwd() . DIRECTORY_SEPARATOR . $this->templateName;
    }

    /**
     * Copies the template .env file.
     *
     * @param $envTemplate
     *
     * @throws UnexpectedValueException
     */
    private function createEnv($envTemplate)
    {
        if (!@copy($envTemplate, $this->fileName)) {
            throw new UnexpectedValueException("Could not read from {$envTemplate}.");
        }
    }
}
