<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 16:00
 */

namespace PbxParser\Entity;

class Section extends Dictionary implements DictionaryContent
{
    private $name;

    /**
     * Section constructor.
     *
     * @param $name
     */
    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    public function getPath() {
        return $this->getParent()->getPath() . " #" . $this->name;
    }

    public function equal(DefineValue $val) {
        if (!$val instanceof Section) {
            return false;
        }

        return $val->getName() == $this->getName() && parent::equal($val);
    }

}