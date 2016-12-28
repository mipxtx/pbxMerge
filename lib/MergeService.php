<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 26.12.16
 * Time: 11:51
 */

namespace PbxParser;

use PbxParser\Entity\DefineStatements;
use PbxParser\Entity\File;

class MergeService
{
    /**
     * @param File[] $files
     * @return File
     */
    public function merge(array $files) {

        $out = array_shift($files);
        foreach ($files as $file) {
            $this->mergeDefines($out, $file);
        }

        return $out;
    }

    /**
     * @param DefineStatements $target
     * @param DefineStatements $def
     * @throws Exception
     */
    private function mergeDefines(DefineStatements $target, DefineStatements $def) {

        foreach ($def->getItems() as $key => $value) {
            if (!$target->hasKey($key)) {
                $target->addItem($value);
            } else {
                $newTarget = $target->getByKey($key);
                if ($newTarget instanceof DefineStatements && $value instanceof DefineStatements) {
                    $this->mergeDefines($newTarget, $value);
                } else {
                    throw new Exception('types mismatch');
                }
            }
        }
    }
}