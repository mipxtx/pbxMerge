<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 22.12.16
 * Time: 11:04
 */

namespace PbxParser\Entity;

class ValueArray implements DefineValue
{
    /**
     * @var Value[]
     */
    private $items = [];

    public function addItem(Value $item) {
        $this->items[] = $item;
    }

    /**
     * @return Value[]
     */
    public function getItems(): array {
        return $this->items;
    }



}