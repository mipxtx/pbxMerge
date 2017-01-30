<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 12:19
 */

namespace PbxParser\Command;

use PbxParser\Exception;
use PbxParser\Service;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Setup extends AbstractCommand
{
    public function __construct($name = null) {
        parent::__construct($name);
        $this->setDescription('setup git hooks for import & export project file');
        $this->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'path to pbxproj. use "find . | grep project.pbxproj" to find project file');

    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $service = new Service();

        $path =  $input->getOption('path');

        if (!$path) {
            throw new Exception('path required, use "find . | grep project.pbxproj" to find it');
        }
        $service->setup($path);
        return 0;
    }
}