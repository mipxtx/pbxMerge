<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 22:33
 */

namespace PbxParser\Entity;

class Value implements DefineValue
{
    use LinksTrait;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $comment;

    /**
     * Value constructor.
     *
     * @param $value
     * @param $comment
     */
    public function __construct(string $value, string $comment = null) {
        $this->value = $value;
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getValue():string {
        return $this->value;
    }

    /**
     * @return null
     */
    public function getComment() {
        return $this->comment;
    }

    public function getPath() {
        return $this->getParent()->getPath() . ' ' . $this->value;
    }

    /**
     * @param DefineValue $val
     * @return bool
     */
    public function equal(DefineValue $val) {
        if (!$val instanceof Value) {
            return false;
        }
        return $this->getValue() == $val->getValue() && $this->getComment() == $val->getComment();
    }

    /**
     * @return DefineValue[]
     */
    public function getChildren() {
        return [];
    }
}