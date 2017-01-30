<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 13:03
 */

namespace PbxParser;

use PbxParser\Entity\File;

class Service
{
    const FILE_NAME = 'project.pbxproj';
    const PARTS_DIR = 'pbx_parts';

    /**
     * @param $path
     * @param $name
     * @throws Exception
     * @return array files has changes
     */
    public function export($path, $name) {
        $parser = new Parser();
        $merge = new MergeService();
        $dumper = new Dumper();

        $dir = $this->getFullPath($path);

        $path = $dir . "/" . self::FILE_NAME;
        if (!file_exists($path)) {
            throw new Exception('file not found:' . $path);
        }

        $timeStart = microtime(1);
        $origin = $parser->parse($path);

        /** @var File[] $files */
        $files = [];
        $partsDir = $dir . "/" . self::PARTS_DIR;

        if (!file_exists($partsDir)) {
            mkdir($partsDir);
        }

        foreach (scandir($partsDir) as $file) {
            if ($file[0] == ".") {
                continue;
            }
            $files[$file] = $parser->parse($partsDir . "/" . $file);
        }

        $timeParse = microtime(1);

        $processor = new Processor($origin, $files, $name);

        $out = $processor->process();

        $timeProcess = microtime(1);

        if ($out) {
            if (isset($files[$name])) {
                $files[$name] = $merge->merge([$files[$name], $out]);
            } else {
                $files[$name] = $out;
            }
        }
        $timeMerge = microtime(1);
        $out = [];

        foreach ($files as $name => $file) {
            $fileName = $partsDir . "/" . $name;
            $origin = file_exists($fileName) ? file_get_contents($fileName) : "";
            $text = $dumper->dump($file);
            if ($origin != $text) {
                $out[] = $fileName;
                file_put_contents($fileName, $text);
            }
        }
        $timeDump = microtime(1);

        //echo "parse:" . round($timeParse - $timeStart,2) . "s\n";
        //echo "process:" . round($timeProcess- $timeParse,2) . "s\n";
        //echo "merge:" . round($timeMerge - $timeProcess,2) . "s\n";
        //echo "dump:" . round($timeDump - $timeMerge,2) . "s\n";

        return $out;
    }

    public function import($path) {
        $merge = new MergeService();
        $dumper = new Dumper(false);
        $parser = new Parser();

        $dir = $this->getFullPath($path) . "/" . self::PARTS_DIR . "/";
        $files = [];

        if (!file_exists($dir)) {
            throw new Exception('parts dir not found at ' . $this->getFullPath($path) . '. use export first');
        }

        foreach (scandir($dir) as $file) {
            if ($file[0] != ".") {
                $files[] = $parser->parse($dir . $file);
            }
        }

        if (!$files) {
            throw new Exception('parts not found at ' . $this->getFullPath($path) . '. use export first');
        }

        $dump = $merge->merge($files);

        file_put_contents($this->getFullPath($path) . "/" . self::FILE_NAME, $dumper->dump($dump));

        return 0;
    }

    private function getFullPath($path) {
        $dir = getcwd() . "/";
        if ($path) {
            if ($path[0] == "/") {
                $dir = $path;
            } else {
                $dir .= $path;
            }
        }
        //$dir = realpath($dir);
        if (!is_dir($dir)) {
            $dir = dirname($dir);
        }

        return $dir;
    }

    private function dumpDiff($name, File $file) {
        $dumper = new Dumper();
        $tmp = tempnam("/tmp", 'diff');
        file_put_contents($tmp, $dumper->dump($file));
        unlink($tmp);
    }

    public function setup($path) {
        $files['export'] = <<<'EXP'
#!/bin/bash
name=`git branch  | grep \* | awk '{print $2}'`
folder=:path:
files=`./pbx.phar export --path=$folder --name=$name`
if [ "$?" -ne "0" ]
then
    echo $files;
    exit 1;
fi


count=0;
for file in $files
do
	git add $file
	count=`expr $count + 1`
done;

if [ "$count" -ne "0" ]
then 
	echo "pbx changed & files add to git index";
	exit 1;
fi
EXP;

        $files['import'] = <<<'EXP'
#!/bin/bash
./pbx.phar import --path=:path:
EXP;

        $files['setup'] = <<<'EXP'
#!/bin/bash
./pbx.phar setup --path=:path:
EXP;


        $cpath = getcwd();

        if (!file_exists($cpath . "/" . $path)) {
            throw new Exception('file not found: ' . $path);
        }

        if (!file_exists($cpath . "/pbx.phar")) {
            throw new Exception('you should run setup at the same folder as pbx.phar');
        }

        exec('git --version', $out, $return);
        if ($return) {
            throw new Exception('git not found (git --version failed).');
        }

        if (!file_exists($cpath . '/.git')) {
            throw new Exception('you should put pbx.phar at the root of your git repo');
        }

        foreach ($files as $name => $text) {
            $file = $cpath . "/" . $name;
            file_put_contents($file, str_replace(':path:', $path, $text));
            chmod($file, 0775);
            exec('git add ' . $file);
        }

        $this->link('pre-commit', 'export');
        $this->link('pre-checkout', 'export');
        $this->link('post-merge', 'import');
        $this->link('post-checkout', 'import');

        exec('git add pbx.phar');
    }

    private function link($hook, $name) {
        $link = getcwd() . "/.git/hooks/{$hook}";

        if (file_exists($link)) {
            unlink($link);
        }
        symlink("../../{$name}", $link);
    }
}


