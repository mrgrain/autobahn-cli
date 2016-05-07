<?php
namespace Autobahn\Cli;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Application
 * @package Autobahn\Cli
 */
class Application extends \Symfony\Component\Console\Application
{
    /**
     * @inheritdoc
     */
    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        parent::configureIO($input, $output);

        // code style
        $style = new OutputFormatterStyle('black', 'white');
        $output->getFormatter()->setStyle('code', $style);
    }
}
