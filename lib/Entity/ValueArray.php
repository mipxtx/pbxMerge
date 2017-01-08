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
    use LinksTrait;

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
    public function getItems() {
        return $this->items;
    }

    public function getPath() {
        return $this->getParent()->getPath() . " (";
    }

    /**
     * @param DefineValue $val
     * @return bool
     */
    public function equal(DefineValue $val) {
        if (!$val instanceof ValueArray) {
            return false;
        }
        if (count($val->getItems()) != count($this->items)) {
            return false;
        }

        foreach ($val->getItems() as $i => $item) {
            if (!isset($this->items[$i]) || !$this->items[$i]->equal($item)) {
                return false;
            }
        }

        return true;
    }

    public function resort(){
        usort(
            $this->items,
            function (Value $a, Value $b) {
                $v1 = trim($a->getComment())?$a->getComment():$a->getValue();
                $v2 = trim($b->getComment())?$b->getComment():$b->getValue();
                return strcmp($v1, $v2);
            }
        );
    }

    /**
     * @return Value[]
     */
    public function getChildren() {
        return $this->items;
    }

    public function removeValue(Value $val) {
        foreach ($this->items as $i => $item) {
            if ($val->getValue() == $item->getValue()) {
                unset($this->items[$i]);
                $this->items = array_values($this->items);

                return;
            }
        }
    }

    /**
     * @return ValueArray
     */
    public function _clone(){
        return $this->cloneLinks(new ValueArray());

    }
}