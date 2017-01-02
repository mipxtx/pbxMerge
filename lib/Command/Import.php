<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 12:19
 */

namespace PbxParser\Command;

use PbxParser\Service;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Import extends AbstractCommand
{
    public function __construct($name = null) {
        parent::__construct($name);
        $this->setDescription('import project.pbxproj to separate files');
        $this->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'path to pbxproj');

    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $service = new Service();
        $service->import($input->getOption('path'));
        return 0;
    }
}