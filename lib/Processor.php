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
    private $myFile;

    /**
     * @var File
     */
    private $files = [];

    private $merge;

    /**
     * Processor constructor.
     *
     * @param File $original
     * @param File[] $files
     */
    public function __construct(File $original, array $files, $myName) {
        $this->original = $original;
        if (isset($files[$myName])) {
            $myFile = $files[$myName];
            unset($files[$myName]);
        } else {
            $myFile = new File($original->getHeading(), 'new file');
        }
        $this->myFile = $myFile;
        $this->files = $files;

        $this->merge = new MergeService();
    }

    public function process() {
        $files = $this->files;
        $files[$this->myFile->getName()] = $this->myFile;
        $merge = new File($this->original->getHeading(), 'common merge');
        $this->merge->mergeFiles($merge, $files);


        $file = $this->compare($this->original, $merge);

        return $file;
    }

    /**
     * @param DefineValue $origin
     * @param DefineValue $parts
     * @return null|DefineValue
     * @throws Exception
     */
    public function compare(DefineValue $origin, DefineValue $parts) {
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
                    return $this->compareDefine($origin, $parts);
                case Dictionary::class:
                    return $this->compareDefineStatements($origin, $parts);
                case Section::class:
                    return $this->compareSections($origin, $parts);
                case ValueArray::class:
                    return $this->compareValueArray($origin, $parts);
                case Value::class:
                    return $this->compareValue($origin, $parts);
                default:
                    throw new Exception('got ' . get_class($origin) . " at " . $origin->getPath());
            }
        }
    }

    public function compareDefine(Define $origin, Define $parts) {
        $val = $this->compare($origin->getValue(), $parts->getValue());
        if ($val) {
            $def = new Define($origin->getKey(), $val);

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

        return $this->compareDefContent($origin, $parts, $out);
    }

    /**
     * @param Section $origin
     * @param Section $parts
     * @return Section
     */
    public function compareSections(Section $origin, Section $parts) {
        $out = new Section($origin->getName());

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
                $res = $this->compare($originItem, $partsItem);
                if ($res) {
                    $container->addItem($res);
                }
            }
        }

        return $container;
    }

    public function compareDefineStatements(Dictionary $origin, Dictionary $parts) {
        $container = new Dictionary();

        return $this->compareDefContent($origin, $parts, $container);
    }

    /**
     * @param ValueArray $origin
     * @param ValueArray $parts
     * @return ValueArray
     * @throws Exception
     */
    public function compareValueArray(ValueArray $origin, ValueArray $parts) {
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