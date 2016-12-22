<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 22.12.16
 * Time: 12:27
 */

namespace PbxParser;

use PbxParser\Entity\Define;
use PbxParser\Entity\DefineValue;
use PbxParser\Entity\File;
use PbxParser\Entity\Section;
use PbxParser\Entity\Value;
use PbxParser\Entity\ValueArray;

class Dumper
{
    public function dump(File $file, $path) {
        $str = "";

        foreach ($file->getSections() as $section) {
            $str .= $this->dumpSection($section);
        }
    }

    public function dumpSection(Section $section) {
        $ret = "/* Begin " . $section->getName() . " */\n";
        foreach ($section->getItems() as $item) {
            $ret .= $this->dumpDefine($item);
        }
        $ret .= "/* End " . $section->getName() . " */\n";

        return $ret;
    }

    public function dumpDefine(Define $define) {
        return $this->dumpValue($define->getKey()) . " = " . $this->dumpDefineValue($define->getValue()) . ";";
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

    public function dumpDefineValue(DefineValue $df) {
        switch (get_class($df)) {
            case Value::class :
                return $this->dumpValue($df);
            case ValueArray::class :
                return $this->dumpValueArray($df);
        }
    }
}