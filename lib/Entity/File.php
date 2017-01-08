<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 22:34
 */

namespace PbxParser\Entity;

class File extends Dictionary
{
    private $heading;

    private $name;

    /**
     * File constructor.
     *
     * @param string $heading
     */
    public function __construct($heading, $name) {
        $this->heading = $heading;
        $this->file = $this;
        $this->parent = $this;
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getHeading() {
        return $this->heading;
    }

    public function getPath() {
        return "->";
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    public function addName($name){
        $this->name .= " merged " . $name;
    }
}