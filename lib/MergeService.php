<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 26.12.16
 * Time: 11:51
 */

namespace PbxParser;

use PbxParser\Entity\Define;
use PbxParser\Entity\Dictionary;
use PbxParser\Entity\DefineValue;
use PbxParser\Entity\File;
use PbxParser\Entity\Section;
use PbxParser\Entity\ValueArray;

class MergeService
{
    /**
     * @param File[] $files
     * @return File
     */
    public function mergeFiles(array $files) {
        $out = array_shift($files);
        foreach ($files as $name => $file) {
            $this->mergeDefineStatements($out, $file);
        }

        return $out;
    }

    public function merge(DefineValue $target, DefineValue $object) {
        if (get_class($target) != get_class($object)) {
            throw new Exception('types mismatch at ' . $target->getPath());
        }
        switch (get_class($target)) {
            case Section::class:
            case Dictionary::class:
                $this->mergeDefineStatements($target, $object);
                break;
            case Define::class:
                $this->mergeDefines($target, $object);
                break;
            case ValueArray::class:
                $this->mergeArrays($target, $object);
                break;
            default :
                throw new Exception('unknown type: ' . get_class($target) . " at " . $target->getPath());
        }
    }

    private function mergeDefines(Define $target, Define $object) {
        $this->merge($target->getValue(), $object->getValue());
    }

    /**
     * @param Dictionary $target
     * @param Dictionary $object
     * @throws Exception
     */
    private function mergeDefineStatements(Dictionary $target, Dictionary $object) {

        foreach ($object->getItems() as $key => $value) {
            if (!$target->hasKey($key)) {
                $target->addItem($value);
            } else {
                $newTarget = $target->getByKey($key);
                $this->merge($newTarget, $value);
            }
        }
    }

    private function mergeArrays(ValueArray $target, ValueArray $object){
        foreach($object->getItems() as $item){
            $target->addItem($item);
        }
    }

}