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
use PbxParser\Entity\Value;
use PbxParser\Entity\ValueArray;

class MergeService
{
    /**
     * @param DefineValue[] $objects
     * @return mixed
     * @throws Exception
     */
    public function merge(array $objects) {
        $base = $objects[0];
        $classes = [];

        foreach ($objects as $object) {
            $classes[get_class($object)] = 1;
        }

        if (count($classes) != 1) {
            throw new Exception('types mismatch at ' . $base->getPath());
        }

        if (!is_object($base)) {
            throw new Exception('expects DefineValue, got ' . var_export($base, 1));
        }

        switch (get_class($base)) {
            case Section::class:
            case Dictionary::class:
            case File::class:
                return $this->mergeDictionary($objects);
                break;
            case Define::class:
                return $this->mergeDefines($objects);
                break;
            case ValueArray::class:
                return $this->mergeArrays($objects);
                break;
            case Value::class:
                return $this->mergeValue($objects);
                break;
            default :
                $ex = new Exception(
                    'unknown type: ' . get_class($base)
                    . " at " . $base->getPath() . " on " . $base->getFile()->getName()
                );
                throw $ex;
        }
    }

    /**
     * @param Define[] $objects
     * @return Define
     */
    private function mergeDefines(array $objects) {
        $base = $objects[0]->_clone();
        $objs = [];
        foreach ($objects as $obj) {
            $objs[] = $obj->getValue();
        }
        $merge = $this->merge($objs);
        $base->init($objects[0]->getKey(), $merge);

        return $base;
    }

    /**
     * @param Dictionary[] $objects
     * @return Dictionary
     */
    private function mergeDictionary(array $objects) {
        $out = $objects[0]->_clone();
        $children = [];
        foreach ($objects as $object) {
            foreach ($object->getChildren() as $key => $child) {
                $children[$key][] = $child;
            }
        }

        foreach ($children as $key => $pack) {
            $merge = $this->merge($pack);
            if($merge instanceof Value){

                echo get_class($pack[0]) . "\n";
            }
            $out->addItem($merge);
        }

        return $out;
    }

    /**
     * @param ValueArray[] $objects
     * @return ValueArray
     */
    private function mergeArrays(array $objects) {
        $base = $objects[0];
        $out = $base->_clone();

        /** @var ValueArray $object */
        foreach ($objects as $object) {
            foreach ($object->getChildren() as $item) {
                $out->addItem($item);
            }
        }

        return $out;
    }

    /**
     * @param Value[] $objects
     * @return Value ;
     * @throws Exception
     */
    private function mergeValue(array $objects) {
        $base = $objects[0];

        if (count($objects) != 1) {
            throw new Exception('trying to merge values at ' . $base->getPath());
        }

        return $base;
    }
}