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

        $out = [];


        foreach ($files as $name => $file) {
            $fileName = $partsDir . "/" . $name;
            $origin = file_exists($fileName) ? file_get_contents($fileName) : "";
            $text = $dumper->dump($file);
            if ($origin != $text) {
                $out[] = $fileName;
                file_put_contents($fileName, $text);
                $result = true;
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

        $merged = $merge->mergeFiles($files);

        file_put_contents($this->getFullPath($path) . "/" . self::FILE_NAME, $dumper->dump($merged));

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
}