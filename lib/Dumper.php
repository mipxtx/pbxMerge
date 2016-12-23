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

    private $forceArray = true;

    const MAX_LENGTH = 150;

    public function dump(File $file) {
        $str = $file->getHeading() . "\n";
        $str .= $this->dumpStatement($file);

        return $str . "\n";
    }

    public function dumpSection(Section $section) {
        $ret = "\n/* Begin " . $section->getName() . " section */\n";
        $out = [];

        foreach ($section->getItems() as $item) {
            $str = $this->getIdent($this->level) . $this->dumpDefineValue($item);
            $out[] = $str;
        }

        $ret .= implode("\n", $out);
        $ret .= "\n/* End " . $section->getName() . " section */";

        return $ret;
    }

    public function dumpDefine(Define $define) {
        $ret = "";
        $ret .= $this->dumpValue($define->getKey()) . " = " . $this->dumpDefineValue($define->getValue()) . ";";

        return $ret;
    }

    public function dumpValue(Value $value) {
        return $value->getValue() . ($value->getComment() ? " /* " . $value->getComment() . " */" : "");
    }

    public function dumpValueArray(ValueArray $va) {
        $ret = "(";
        $this->level++;
        $out = [];
        foreach ($va->getItems() as $item) {
            $out[] = $this->dumpValue($item);
        }

        $str = implode(", ", $out) . ",";

        $ident = "";

        if (mb_strlen($str) > self::MAX_LENGTH || $this->forceArray) {
            $ident = "\n" . $this->getIdent($this->level);
            $str = implode("," . $ident, $out);
            if($out){
                $str = $ident . $str . ",";
            }
        }

        $ret .= $str;

        $this->level--;
        if ($ident) {
            $ret .= "\n" . $this->getIdent($this->level);
        }
        $ret .= ")";

        return $ret;
    }

    public function dumpStatement(DefineStatements $ds) {
        $ret = '{';
        $this->level++;
        $out = [];

        foreach ($ds->getItems() as $item) {
            $out[] = $this->dumpDefineValue($item);
        }
        $str = implode(' ', $out) . " ";

        $ident = "";
        if (mb_strlen($str) > self::MAX_LENGTH) {
            $ident = $this->getIdent($this->level);
            $str = "\n{$ident}" . implode("\n{$ident}", $out);
        }

        $ret .= $str;
        $this->level--;

        if ($ident) {
            $ret .= "\n" . $this->getIdent($this->level);
        }
        $ret .= "}";

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

    public function getIdent($count) {
        $out = "";
        for ($i = 0; $i < $count; $i++) {
            $out .= "\t";
        }

        return $out;
    }
}