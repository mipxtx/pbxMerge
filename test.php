<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 22.12.16
 * Time: 12:09
 */


$fileName = __DIR__ . "/project.pbxproj";
//$fileName = getcwd() . "/" . $argv[1] . "\n";

include __DIR__ . "/vendor/autoload.php";
$parser = new \PbxParser\Parser();
$file = $parser->parse($fileName);
$dumper = new \PbxParser\Dumper();
echo $dumper->dump($file);
