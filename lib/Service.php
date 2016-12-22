<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 13:03
 */

namespace PbxParser;

use PbxParser\Entity\Section;

class Service
{
    const FILE_NAME = 'project.pbxproj';

    public function export($arg = null) {

        $path = getcwd() . "/";

        if ($arg) {
            if ($arg[0] == "/") {
                $path = $arg;
            } else {
                $path .= $arg;
            }
        }

        if (is_dir($path)) {
            $path .= "/" . self::FILE_NAME;
        }

        if (!file_exists($path)) {
            throw new Exception('file not found:' . $path);
        }

        $parser = new Parser();

        $file = $parser->parse($path);


    }


}