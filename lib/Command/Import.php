<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 12:19
 */

namespace PbxParser\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Import extends AbstractCommand
{
    public function __construct($name = null) {
        parent::__construct($name);
        $this->setDescription('');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $path = getcwd() . "/";

        if($input->hasArgument('path')) {
            $arg = $input->getArgument('path');

            if($arg[0] == "/"){
                $path = $arg;
            }else{
                $path .= $arg;
            }
        }

        var_dump($path);

        return 0;
    }
}