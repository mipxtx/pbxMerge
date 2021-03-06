<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 23.12.16
 * Time: 14:37
 */

namespace PbxParser;

use PbxParser\Entity\File;
use PbxParser\Entity\Define;
use PbxParser\Entity\Section;
use PbxParser\Entity\DefineValue;
use PbxParser\Entity\Dictionary;
use PbxParser\Entity\Value;
use PbxParser\Entity\ValueArray;

class Processor
{
    /**
     * @var File
     */
    private $original;

    /**
     * @var File
     */
    private $files = [];

    private $merge;

    private $file;

    /**
     * Processor constructor.
     *
     * @param File $original
     * @param File[] $files
     * @param $myName
     */
    public function __construct(File $original, array $files, $myName) {
        $this->original = $original;
        $this->files = $files;
        $this->merge = new MergeService();
    }

    public function process() {
        $this->file = null;
        if ($this->files) {
            $merge = $this->merge->merge(array_values($this->files));
        } else {
            $merge = new File($this->original->getHeading(), '');
        }
        $file = $this->compare($this->original, $merge, null);

        return $file;
    }

    /**
     * @param DefineValue $origin
     * @param DefineValue $parts
     * @return null|DefineValue
     * @throws Exception
     */
    public function compare(DefineValue $origin, DefineValue $parts, $parent) {
        if ($origin->equal($parts)) {
            return null;
        } else {
            if (get_class($origin) != get_class($parts)) {
                throw new Exception('type mismatch: ' . $origin->getPath() . "/" . $parts->getPath());
            }
            switch (get_class($origin)) {
                case File::class:
                    return $this->compareFiles($origin, $parts);
                case Define::class :
                    return $this->compareDefine($origin, $parts, $parent);
                case Dictionary::class:
                    return $this->compareDefineStatements($origin, $parts, $parent);
                case Section::class:
                    return $this->compareSections($origin, $parts, $parent);
                case ValueArray::class:
                    return $this->compareValueArray($origin, $parts, $parent);
                case Value::class:
                    return $this->compareValue($origin, $parts);
                default:
                    throw new Exception('got ' . get_class($origin) . " at " . $origin->getPath());
            }
        }
    }

    public function compareDefine(Define $origin, Define $parts, DefineValue $parent) {
        $def = new Define();
        $def->setLinks($parent);
        $val = $this->compare($origin->getValue(), $parts->getValue(), $def);
        if ($val) {


            $def->init($origin->getKey(), $val);

            return $def;
        }

        return null;
    }

    /**
     * @param File $origin
     * @param File $parts
     * @return File
     */
    public function compareFiles(File $origin, File $parts) {
        $out = new File($origin->getHeading(), 'compare');
        $this->file = $out;

        return $this->compareDefContent($origin, $parts, $out);
    }

    /**
     * @param Section $origin
     * @param Section $parts
     * @return Section
     */
    public function compareSections(Section $origin, Section $parts, DefineValue $parent) {
        $out = new Section($origin->getName());
        $out->setLinks($parent);

        return $this->compareDefContent($origin, $parts, $out);
    }

    /**
     * @param Dictionary $origin
     * @param Dictionary $parts
     * @param Dictionary $container
     * @return Dictionary
     */
    public function compareDefContent(Dictionary $origin, Dictionary $parts, Dictionary $container) {

        $newKeys = array_diff($origin->getKeys(), $parts->getKeys());
        $missedKeys = array_diff($parts->getKeys(), $origin->getKeys());
        $common = array_intersect($parts->getKeys(), $origin->getKeys());

        foreach ($newKeys as $key) {
            $container->addItem($origin->getByKey($key));
        }

        foreach ($missedKeys as $key) {
            $parts->getByKey($key)->getParent()->removeByKey($key);
        }

        foreach ($common as $key) {
            $originItem = $origin->getByKey($key);
            if (!$parts->hasKey($key)) {
                $container->addItem($originItem);
            } else {
                $partsItem = $parts->getByKey($key);
                if ($partsItem->equal($originItem)) {
                    continue;
                }
                $res = $this->compare($originItem, $partsItem, $container);
                if ($res) {
                    $container->addItem($res);
                }
            }
        }

        return $container;
    }

    public function compareDefineStatements(Dictionary $origin, Dictionary $parts, DefineValue $parent) {
        $container = new Dictionary();
        $container->setLinks($parent);

        return $this->compareDefContent($origin, $parts, $container);
    }

    /**
     * @param ValueArray $origin
     * @param ValueArray $parts
     * @return ValueArray
     * @throws Exception
     */
    public function compareValueArray(ValueArray $origin, ValueArray $parts, DefineValue $parent) {
        /** @var DefineValue[] $originItems */
        $originItems = [];
        foreach ($origin->getItems() as $item) {
            if ($item instanceof Value) {
                $originItems[$item->getValue()] = $item;
            } else {
                throw new Exception('Array content: ' . get_class($item) . " at " . $origin->getPath());
            }
        }

        /** @var DefineValue[] $partsItems */
        $partsItems = [];
        foreach ($parts->getItems() as $item) {
            if ($item instanceof Value) {
                $partsItems[$item->getValue()] = $item;
            } else {
                throw new Exception('Array content: ' . get_class($item) . " at " . $origin->getPath());
            }
        }

        $out = new ValueArray();
        $out->setLinks($parent);

        $newKeys = array_diff(array_keys($originItems), array_keys($partsItems));
        foreach ($newKeys as $key) {
            $out->addItem($originItems[$key]);
        }

        $oldKeys = array_diff(array_keys($partsItems), array_keys($originItems));
        foreach ($oldKeys as $key) {
            $parts->removeValue($partsItems[$key]);
        }

        $commonKeys = array_intersect(array_keys($partsItems), array_keys($originItems));
        foreach ($commonKeys as $key) {
            $orig = $originItems[$key];
            $prts = $partsItems[$key];
            if (!$orig->equal($prts)) {
                $out->addItem($orig);
                $parts->removeValue($prts);
            } else {
            }
        }

        if (count($out->getChildren())) {
            return $out;
        } else {
            return null;
        }
    }

    /**
     * @param Value $origin
     * @param Value $parts
     * @return Value
     */
    public function compareValue(Value $origin, Value $parts) {
        /** @var Define $parent */
        $parent = $parts->getParent();
        $key = $parent->getKey()->getValue();

        /** @var Dictionary $holder */
        $holder = $parent->getParent();
        $holder->removeByKey($key);

        return $origin;
    }
}