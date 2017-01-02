#!/usr/bin/php -d phar.readonly=0
<?php
/**
 * Created by PhpStorm.
 * User: yarullin
 * Date: 30.09.16
 * Time: 14:29
 */

$array = [];
$array+=getFiles('lib');
$array+=getFiles('vendor');
$array+=getFiles('main.php');

$pharFile = __DIR__ . "/build/pbx.phar";

if(file_exists($pharFile)){
    unlink($pharFile);
}

$phar = new Phar($pharFile);
$phar->startBuffering();
$phar->buildFromIterator(new ArrayIterator($array));
$phar->setDefaultStub('main.php');
$phar->setStub("#!/usr/bin/php \n" . $phar->getStub());
$phar->stopBuffering();

chmod($pharFile, 0755);

function getFiles($file) {
    $path = __DIR__ . "/" . $file;
    $out = [];
    if (is_dir($file)) {
        foreach (scandir($path) as $subfile) {
            if ($subfile[0] == ".") {
                continue;
            }

            $out += getFiles($file . "/" . $subfile);
        }
    } else {
        $out[$file] = $path;
    }

    return $out;
}