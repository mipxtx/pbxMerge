<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 22:42
 */

namespace PbxParser\Entity;

class Define implements DefineValue, DictionaryContent
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

    /**
     * Define constructor.
     *
     * @param Value $key
     * @param DefineValue $value
     */
    public function __construct(Value $key, DefineValue $value) {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @return Value
     */
    public function getKey(): Value {
        return $this->key;
    }

    /**
     * @return DefineValue
     */
    public function getValue(): DefineValue {
        return $this->value;
    }

    public function getName(): string {
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
}