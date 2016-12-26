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
    private $keys = [];

    /**
     * @var DefineStatementsContent[]
     */
    private $items = [];

    /**
     * @param DefineStatementsContent $item
     */
    public function addItem(DefineStatementsContent $item) {
        $this->items[] = $item;

        if ($item instanceof Define) {
            $keys[$item->getKey()->getValue()] = $item;
        }
    }

    /**
     * @return DefineStatementsContent[]
     */
    public function getItems(): array {
        return $this->items;
    }

    public function count() {
        return count($this->items);
    }

    public function hasKey($key) {
        return array_key_exists($key, $this->keys);
    }
}