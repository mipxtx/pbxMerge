<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 22:34
 */

namespace PbxParser\Entity;

class File extends DefineStatements
{
    private $heading;

    /**
     * File constructor.
     *
     * @param $heading
     */
    public function __construct($heading) {
        $this->heading = $heading;
    }

    /**
     * @return mixed
     */
    public function getHeading() {
        return $this->heading;
    }



}