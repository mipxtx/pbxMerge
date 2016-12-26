<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 22:42
 */

namespace PbxParser\Entity;

class Define implements DefineValue, DefineStatementsContent
{
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


}