<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 22.12.16
 * Time: 12:27
 */
namespace PbxParser;

use PbxParser\Entity\Define;
use PbxParser\Entity\DefineStatements;
use PbxParser\Entity\DefineValue;
use PbxParser\Entity\File;
use PbxParser\Entity\Section;
use PbxParser\Entity\Value;
use PbxParser\Entity\ValueArray;

class Dumper
{
    private $level = 0;

    public function dump(File $file) {
        $str = $file->getHeading();
        $str .= $this->dumpStatement($file);

        return $str;
    }

    public function dumpSection(Section $section) {
        $ret = "\n/* Begin " . $section->getName() . " section */\n";
        foreach ($section->getItems() as $item) {
            $ret .= $this->dumpDefineValue($item);
        }
        $ret .= "\n/* End " . $section->getName() . " section */\n";

        return $ret;
    }

    public function dumpDefine(Define $define) {
        $ret = "";
        if ($this->level < 3) {
            for ($i = 0; $i < $this->level; $i++) {
                $ret .= "\t";
            }
        }
        $ret .= $this->dumpValue($define->getKey()) . " = " . $this->dumpDefineValue($define->getValue()) . "; ";
        if ($this->level < 3) {

                $ret .= "\n";

        }

        return $ret;
    }

    public function dumpValue(Value $value) {
        return $value->getValue() . ($value->getComment() ? " /* " . $value->getComment() . " */" : "");
    }

    public function dumpValueArray(ValueArray $va) {
        $ret = "(";
        foreach ($va->getItems() as $item) {
            $ret .= $this->dumpValue($item) . ",\n";
        }
        $ret .= ")";

        return $ret;
    }

    public function dumpStatement(DefineStatements $ds) {
        $ret = '{';
        $this->level++;
        foreach ($ds->getItems() as $item) {
            $ret .= $this->dumpDefineValue($item);
        }
        $ret .= "}";
        $this->level--;

        return $ret;
    }

    public function dumpDefineValue(DefineValue $df) {
        switch (get_class($df)) {
            case Value::class :
                return $this->dumpValue($df);
            case ValueArray::class :
                return $this->dumpValueArray($df);
            case DefineStatements::class:
                return $this->dumpStatement($df);
            case Section::class:
                return $this->dumpSection($df);
            case Define::class:
                return $this->dumpDefine($df);
            default:
                throw new Exception(get_class($df) . ' dump desc not found');
        }
    }
}