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


if(file_exists(__DIR__ . "/pbx.phar")){
    unlink(__DIR__ . "/pbx.phar");
}

$phar = new Phar('pbx.phar');
$phar->startBuffering();
$phar->buildFromIterator(new ArrayIterator($array));
$phar->setDefaultStub('main.php');
$phar->setStub("#!/usr/bin/php \n" . $phar->getStub());
$phar->stopBuffering();

chmod('pbx.phar', 0755);

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