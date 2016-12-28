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
use PbxParser\Entity\DefineStatements;

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
            $myFile = new File($original->getHeading());
        }
        $this->myFile = $myFile;
        $this->files = $files;

        $this->merge = new MergeService();
    }

    public function process() {
        $files = $this->files;
        $files[] = $this->myFile;
        $merge = $this->merge->merge($files);
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
                case DefineStatements::class:
                    return $this->compareDefineStatements($origin, $parts);
                case Section::class:
                    return $this->compareSections($origin, $parts);
                default:
                    echo 'got ' . get_class($origin) . " at " . __FILE__ . ":" . __LINE__ . "\n";
                    die();
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
        $out = new File($origin->getHeading());

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
     * @param DefineStatements $origin
     * @param DefineStatements $parts
     * @param DefineStatements $container
     * @return DefineStatements
     */
    public function compareDefContent(DefineStatements $origin, DefineStatements $parts, DefineStatements $container) {

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
                $container->addItem($this->compare($originItem, $partsItem));
            }
        }

        return $container;
    }

    public function compareDefineStatements(DefineStatements $origin, DefineStatements $parts) {
        $container = new DefineStatements();

        return $this->compareDefContent($origin, $parts, $container);
    }
}