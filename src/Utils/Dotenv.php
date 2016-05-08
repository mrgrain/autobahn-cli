<?php
namespace Autobahn\Cli\Utils;

use Autobahn\Cli\Contracts\Dotenv as DotenvContract;
use Dotenv\Exception\InvalidPathException;
use Dotenv\Loader;

/**
 * Class Dotenv
 * @package Autobahn\Cli\Utils
 */
class Dotenv extends Loader implements DotenvContract
{
    /**
     * The list of all environment variables.
     * @var array
     */
    protected $environment_variables = array();

    /**
     * Numbers of the lines where environment variables are stored in the file.
     * @var array
     */
    protected $line_numbers = array();

    /**
     * Dotenv constructor.
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        parent::__construct($filePath);
        if (file_exists($filePath)) {
            $this->load();
        }
    }

    /**
     * Load `.env` file in given directory.
     *
     * @return array
     */
    public function load()
    {
        $this->ensureFileIsReadable();

        $filePath = $this->filePath;
        $lines = $this->readLinesFromFile($filePath);
        foreach ($lines as $line_number => $line) {
            if (!$this->isComment($line) && $this->looksLikeSetter($line)) {
                $this->setEnvironmentVariable($line, $line_number);
            }
        }

        return $lines;
    }

    /**
     * Read lines from the file, auto detecting line endings.
     *
     * @param string $filePath
     *
     * @return array
     */
    protected function readLinesFromFile($filePath)
    {
        // Read file into an array of lines with auto-detected line endings
        $autodetect = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', '1');
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        ini_set('auto_detect_line_endings', $autodetect);

        return $lines;
    }

    /**
     * @param string $name
     * @param null $value
     */
    public function setEnvironmentVariable($name, $value = null)
    {
        $line = $value;
        list($name, $value) = $this->normaliseEnvironmentVariable($name, null);
        $this->environment_variables[$name] = $value;
        $this->line_numbers[$name] = $line;
    }

    /**
     * Set an environment variable to a value.
     * @param string $variable
     * @param mixed $value
     * @param bool $export
     * @return string|void
     */
    public function set($variable, $value = '', $export = false)
    {
        $line = $this->composeLine($variable, $value, $export);
        $number = $this->has($variable) ? $this->line_numbers[$variable] : null;
        $this->writeLine($line, $number);
        $this->load();

        return $line;
    }

    /**
     * Write a line to the dotenv file.
     * @param string $line The line to write
     * @param integer|null $number Write to a specific location
     * @return mixed
     */
    protected function writeLine($line, $number = null)
    {
        $this->ensureFileIsWritable();

        // Append
        if (is_null($number)) {
            return file_put_contents($this->filePath, $line . PHP_EOL, FILE_APPEND);
        }

        // Replace
        $lines = $this->readLinesFromFile($this->filePath);
        $lines[$number] = $line;
        return file_put_contents($this->filePath, implode(PHP_EOL, $lines));
    }

    /**
     * Ensures the given filePath is writable.
     *
     * @throws \Dotenv\Exception\InvalidPathException
     *
     * @return void
     */
    protected function ensureFileIsWritable()
    {
        if ((!is_writable($this->filePath) || !is_file($this->filePath))
            && (!is_dir(dirname($this->filePath)) || !is_writable(dirname($this->filePath)))
        ) {
            throw new InvalidPathException(sprintf('Unable to write the environment file at %s.', $this->filePath));
        }
    }

    /**
     * Does the dotenv file contain a specific environment variable?
     * @param $variable
     * @return bool
     */
    public function has($variable)
    {
        return array_key_exists($variable, $this->environment_variables);
    }

    /**
     * Get the value of an environment variable from the dotenv file.
     * @param $variable
     * @param null $default
     * @return string
     */
    public function get($variable, $default = null)
    {
        if (!$this->has($variable)) {
            return $default;
        }
        return $this->environment_variables[$variable];
    }

    /**
     * Get all environment variables from the dotenv file.
     * @return array
     */
    public function getAll()
    {
        return $this->environment_variables;
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
