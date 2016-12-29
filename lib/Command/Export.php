<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 12:24
 */

namespace PbxParser\Command;

use PbxParser\Service;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Export extends AbstractCommand
{
    const EXT = '.pbxproj';

    public function __construct($name = null) {
        parent::__construct($name);
        $this->setDescription('export project.pbxproj to separate files');
        $this->addOption('path', 'p', InputOption::VALUE_REQUIRED);
        $this->addOption('name', 'a', InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $service = new Service();
        $service->export($input->getOption('path'), $input->getOption('name') . self::EXT);

        return 0;
    }
}