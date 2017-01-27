<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 12:12
 */

ini_set('display_errors', true);
error_reporting(E_ALL & ~E_NOTICE);

use Symfony\Component\Console\Application;
use PbxParser\Command\Import;
use PbxParser\Command\Export;
use PbxParser\Command\Setup;

include __DIR__ . "/vendor/autoload.php";

$application = new Application();
$application->add(new Import('import'));
$application->add(new Export('export'));
$application->add(new Setup('setup'));
$application->run();
