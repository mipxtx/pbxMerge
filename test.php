<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 22.12.16
 * Time: 12:09
 */

use PbxParser\Entity\File;

$origin = 'project.pbxproj';

$folder = 'pbx_parts/';

//$fileName = __DIR__ . "/project.pbxproj";
$path = realpath(getcwd() . "/" . $argv[1]) . "/";

echo "processing in $path\n";

include __DIR__ . "/vendor/autoload.php";
$parser = new \PbxParser\Parser();

$fileName = $path . $origin;
$origin = $parser->parse($fileName);

$files = [];

foreach (scandir($path . $folder) as $file) {
    $files[$file] = $parser->parse($path . $folder . $file);
}




function getDiff($from, $to) {

}

/**
 * @param File[] $files
 */
function merge(array $files){


}


//$dumper = new \PbxParser\Dumper();
//echo $dumper->dump($file);
