<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 13:03
 */

namespace PbxParser;

class Service
{
    const FILE_NAME = 'project.pbxproj';
    const PARTS_DIR = 'pbx_parts';

    public function export($path, $name) {
        $parser = new Parser();
        $merge = new MergeService();
        $dumper = new Dumper();

        $dir = getcwd() . "/";
        if ($path) {
            if ($path[0] == "/") {
                $dir = $path;
            } else {
                $dir .= $path;
            }
        }

        $dir = realpath($dir);
        if (!is_dir($dir)) {
            $dir = dirname($dir);
        }

        $path = $dir . "/" . self::FILE_NAME;
        if (!file_exists($path)) {
            throw new Exception('file not found:' . $path);
        }
        $origin = $parser->parse($path);

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
            if (isset($files[$name])) {
                $files[$name] = $merge->mergeFiles([$files[$name], $out]);
            } else {
                $files[$name] = $out;
            }
        }

        foreach ($files as $name => $file) {
            file_put_contents($partsDir . "/" . $name, $dumper->dump($file));
        }
    }
}