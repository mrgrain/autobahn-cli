<?php
namespace Autobahn\Cli\Contracts;

/**
 * Interface Dotenv
 * Generic access on .env files
 * @package Autobahn\Cli\Contracts
 */
interface Dotenv
{
    /**
     * Set an environment variable to a value.
     * @param string $variable
     * @param  mixed $value
     * @param bool $export
     * @return void
     */
    public function set($variable, $value = '', $export = false);

    /**
     * Does the dotenv file contain a specific environment variable?
     * @param $variable
     * @return bool
     */
    public function has($variable);

    /**
     * Get the value of an environment variable from the dotenv file.
     * @param $variable
     * @return string
     */
    public function get($variable);

    /**
     * Get all environment variables from the dotenv file.
     * @return array
     */
    public function getAll();
}
