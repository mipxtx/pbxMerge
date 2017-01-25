<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 22.12.16
 * Time: 12:27
 */
namespace PbxParser;

use PbxParser\Entity\Define;
use PbxParser\Entity\Dictionary;
use PbxParser\Entity\DefineValue;
use PbxParser\Entity\File;
use PbxParser\Entity\Section;
use PbxParser\Entity\Value;
use PbxParser\Entity\ValueArray;

class Dumper
{
    private $level = 0;

    private $forceArray = true;

    private $forceDict = true;

    private $skipEmpty = true;

    const MAX_LENGTH = 150;

    private $logger;

    public function __construct($forceDict = true, $forceArray = true) {
        $this->forceArray = $forceArray;
        $this->forceDict = $forceDict;
        $this->logger = new Logger();
    }

    public function dump(File $file) {
        $content = $this->dumpDictionary($file);
        if ($this->skipEmpty && !$content) {
            return "";
        }

        return $file->getHeading() . "\n" . $this->dumpDictionary($file) . "\n";
    }

    public function dumpSection(Section $section) {
        $out = [];

        foreach ($section->getItems() as $item) {
            $str = $this->getIdent() . $this->dumpDefineValue($item);
            if ($str) {
                $out[] = $str;
            }
        }
        if ($this->skipEmpty && !$out) {
            return "";
        }

        $ret = "\n/* Begin " . $section->getName() . " section */\n";
        $ret .= implode("\n", $out);
        $ret .= "\n/* End " . $section->getName() . " section */";

        return $ret;
    }

    public function dumpDefine(Define $define) {
        $ret = "";
        $val = $this->dumpDefineValue($define->getValue());

        if (!$val && $this->skipEmpty) {
            return "";
        }
        $ret .= $this->dumpValue($define->getKey()) . " = " . $val . ";";

        return $ret;
    }

    public function dumpValue(Value $value) {
        return $value->getValue() . ($value->getComment() ? " " . $value->getComment() : "");
    }

    public function dumpValueArray(ValueArray $va) {

        if (!$va->getChildren() && $this->skipEmpty) {
            return "";
        }

        $ret = "(";
        $this->incLevel();
        $out = [];
        $va->resort();
        foreach ($va->getItems() as $item) {
            $out[] = $this->dumpValue($item);
        }
        $str = implode(", ", $out) . ",";
        $ident = "";
        if (mb_strlen($str) > self::MAX_LENGTH || $this->forceArray) {
            $ident = "\n" . $this->getIdent();
            $str = implode("," . $ident, $out);
            if ($out) {
                $str = $ident . $str . ",";
            }
        }
        $ret .= $str;
        $this->decLevel();
        if ($ident) {
            $ret .= "\n" . $this->getIdent();
        }
        $ret .= ")";

        return $ret;
    }

    public function dumpDictionary(Dictionary $ds) {
        $out = [];

        $ret = '{';
        $this->incLevel();

        foreach ($ds->getItems() as $item) {
            $str = $this->dumpDefineValue($item);
            if ($str) {
                $out[] = $str;
            }
        }

        if ($this->skipEmpty && !$out) {
            $this->decLevel();
            return "";
        }

        $str = implode(' ', $out) . " ";

        $ident = "";
        if (mb_strlen($str) > self::MAX_LENGTH || strpos($str, "\n") !== false || $this->forceDict) {
            $ident = $this->getIdent();
            $str = "\n{$ident}" . implode("\n{$ident}", $out);
        }

        $ret .= $str;
        $this->decLevel();

        if ($ident) {
            $ret .= "\n" . $this->getIdent();
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
            case Dictionary::class:
                return $this->dumpDictionary($df);
            case Section::class:
                return $this->dumpSection($df);
            case Define::class:
                return $this->dumpDefine($df);
            default:
                throw new Exception(get_class($df) . ' dump desc not found');
        }
    }

    public function getIdent() {
        $out = "";
        for ($i = 0; $i < $this->level; $i++) {
            $out .= "\t";
        }

        return $out;
    }

    private function incLevel(){
        $this->level++;
    }

    private function decLevel(){
        $this->level --;
    }



}