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

    /**
     * File constructor.
     *
     * @param string $heading
     */
    public function __construct($heading) {
        $this->heading = $heading;
        $this->file = $this;
        $this->parent = $this;
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
}