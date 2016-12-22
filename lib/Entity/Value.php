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
    private $value;

    private $comment;

    /**
     * Value constructor.
     *
     * @param $value
     * @param $comment
     */
    public function __construct($value, $comment = null) {
        $this->value = $value;
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @return null
     */
    public function getComment() {
        return $this->comment;
    }


}