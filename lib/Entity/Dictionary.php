<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 22:58
 */

namespace PbxParser\Entity;

class Dictionary implements DefineValue
{
    use LinksTrait;

    /**
     * @var DictionaryContent[]
     */
    private $items = [];

    /**
     * @param DictionaryContent $item
     */
    public function addItem(DictionaryContent $item) {
        $this->items[$item->getName()] = $item;
    }

    /**
     * @return DictionaryContent[]
     */
    public function getItems(){
        return $this->items;
    }

    public function count() {
        return count($this->items);
    }

    public function hasKey($key) {
        return array_key_exists($key, $this->items);
    }

    public function getByKey($key) {
        return $this->items[$key];
    }

    public function removeByKey($key) {
        unset($this->items[$key]);
    }

    public function removeKey($key) {
        unset($this->items[$key]);
    }

    public function getPath() {
        return $this->getParent()->getPath() . " {";
    }

    public function getKeys(){
        $keys = array_keys($this->items);
        asort($keys);
        return $keys;
    }

    /**
     * @param DefineValue $val
     * @return bool
     */
    public function equal(DefineValue $val) {

        if (!$val instanceof Dictionary) {
            return false;
        }

        if(count($this->items) != count($val->getItems())){
            return false;
        }

        if($val->getKeys() != $this->getKeys()){
            return false;
        }

        foreach($this->items as $key => $item){
            if(!$item->equal($val->getByKey($key))){
                return false;
            }
        }
        return true;
    }

    /**
     * @return DefineValue[]
     */
    public function getChildren() {
        return $this->items;
    }

    public function _clone(){
        return $this->cloneLinks(new Dictionary());
    }
}