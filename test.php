<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 22.12.16
 * Time: 12:09
 */

use PbxParser\Entity\File;
include __DIR__ . "/vendor/autoload.php";
$parser = new \PbxParser\Parser();


/*

$origin = 'project.pbxproj';

$folder = 'pbx_parts/';

$myName = 'feature_1';

//$fileName = __DIR__ . "/project.pbxproj";
$path = realpath(getcwd() . "/" . $argv[1]) . "/";

echo "processing in $path\n";


$fileName = $path . $origin;
$origin = $parser->parse($fileName);

$files = [];

foreach (scandir($path . $folder) as $file) {
    if($file[0]!='.') {
        $files[$file] = $parser->parse($path . $folder . $file);
    }
}

if (!isset($files[$myName])) {
    $myFile = new File($origin->getHeading());
} else {
    $myFile = $files[$myName];
    unset($files[$myName]);
}

$processor = new \PbxParser\Processor($origin, $files, $myName);
$processor->process();


//$dumper = new \PbxParser\Dumper();
//echo $dumper->dump($file);
*/




$origin = $parser->parse(__DIR__ . "/sample1.pbxproj");
$parts = $parser->parse(__DIR__ . "/sample2.pbxproj");

$dumper = new \PbxParser\Dumper();
echo "origin:\n";
echo $dumper->dump($origin);
echo "parts:\n";
echo $dumper->dump($parts);

echo "============\n";
$processor = new \PbxParser\Processor($origin, [$parts], 'lll');
$out = $processor->process();
echo "diff:\n";
echo $dumper->dump($out);
echo "new:\n";
echo $dumper->dump($parts);


echo "";

