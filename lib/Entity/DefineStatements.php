<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 22:58
 */

namespace PbxParser\Entity;

class DefineStatements implements DefineValue
{
    /**
     * @var DefineValue[]
     */
    private $items = [];

    /**
     * @param DefineValue $item
     */
    public function addItem(DefineValue $item) {
        $this->items[] = $item;
    }

    /**
     * @return DefineValue[]
     */
    public function getItems(): array {
        return $this->items;
    }

    public function count(){
        return count($this->items);
    }
}