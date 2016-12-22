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
    private $items = [];

    public function addItem(Define $item) {
        $this->items[] = $item;
    }

    /**
     * @return Define[]
     */
    public function getItems(): array {
        return $this->items;
    }



}