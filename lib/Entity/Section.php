<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 16:00
 */

namespace PbxParser\Entity;

class Section extends DefineStatements
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


}