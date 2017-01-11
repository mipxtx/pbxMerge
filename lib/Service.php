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

        if (!is_dir($dir)) {
            $dir = dirname($dir);
        }

        $path = $dir . "/" . self::FILE_NAME;
        if (!file_exists($path)) {
            throw new Exception('file not found:' . $path);
        }
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

        $processor = new Processor($origin, $files, $name);

        $out = $processor->process();

        if ($out) {
            error_log('changes: ' . $dumper->dump($out));
            if (isset($files[$name])) {
                error_log("merged changes to $name");
                $files[$name] = $merge->merge([$files[$name], $out]);
            } else {
                error_log("new file $name");
                $files[$name] = $out;
            }
        }

        $out = [];

        foreach ($files as $name => $file) {
            $fileName = $partsDir . "/" . $name;
            $origin = file_exists($fileName) ? file_get_contents($fileName) : "";
            $text = $dumper->dump($file);
            if ($origin != $text) {
                error_log('dumping ' . $file->getName() . ' as ' . $name);
                $out[] = $fileName;
                file_put_contents($fileName, $text);
            }
        }

        return $out;
    }

    public function import($path) {
        $merge = new MergeService();
        $dumper = new Dumper(false);
        $parser = new Parser();

        $dir = $this->getFullPath($path) . "/" . self::PARTS_DIR . "/";
        $files = [];

        foreach (scandir($dir) as $file) {
            if ($file[0] != ".") {
                $files[] = $parser->parse($dir . $file);
            }
        }

        $dump = new File($files[0]->getHeading(), 'dump');

        $merge->mergeFiles($dump, $files);

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
        $dir = realpath($dir);

        return $dir;
    }


    private function dumpDiff($name, File $file){
        $dumper = new Dumper();
        $tmp = tempnam("/tmp", 'diff');
        file_put_contents($tmp, $dumper->dump($file));
        error_log(`diff -u $name $tmp`);
        unlink($tmp);

    }
}