#!/usr/bin/php -d phar.readonly=0
<?php
/**
 * Created by PhpStorm.
 * User: yarullin
 * Date: 30.09.16
 * Time: 14:29
 */

$phar = new Phar('pbx.phar');

$phar->startBuffering();
$phar->buildFromDirectory('.');
$phar->setDefaultStub('main.php');
$phar->setStub("#!/usr/bin/php \n" . $phar->getStub());
$phar->stopBuffering();

chmod('pbx.phar', 0755);