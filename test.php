<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 22.12.16
 * Time: 12:09
 */

include __DIR__ . "/vendor/autoload.php";
$parser = new \PbxParser\Parser();
$file = $parser->parse(__DIR__ . "/project.pbxproj");


$dumper = new \PbxParser\Dumper();

echo $dumper->dump($file);