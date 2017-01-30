<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 22:42
 */

namespace PbxParser\Entity;

use PbxParser\Logger;
use PbxParser\LoggerElement;

class Define implements DefineValue, DictionaryContent, LoggerElement
{
    use LinksTrait;

    /**
     * @var Value
     */
    private $key;

    /**
     * @var DefineValue
     */
    private $value;


    public function init(Value $key, DefineValue $value) {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @return Value
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * @return DefineValue
     */
    public function getValue() {
        return $this->value;
    }

    public function getName() {
        if (!$this->key) {
            print_r($this);

            error_log((new Logger())->buildTrace(debug_backtrace()));
        }

        return $this->getKey()->getValue();
    }

    public function getPath() {
        return $this->parent->getPath() . " <" . $this->key->getValue() . ">";
    }

    /**
     * @param DefineValue $val
     * @return bool
     */
    public function equal(DefineValue $val) {
        $res =
            $val instanceof Define
            && $this->getKey()->equal($val->getKey())
            && $this->getValue()->equal($val->getValue());

        return $res;
    }

    /**
     * @return DefineValue[]
     */
    public function getChildren() {
        return [
            $this->key,
            $this->value
        ];
    }

    /**
     * @return Define
     */
    public function _clone() {
        return $this->cloneLinks(new Define());
    }

    public function getLoggerId() {
        if (!$this->getKey()) {
            return '';
        }

        return $this->getKey()->getValue();
    }
}