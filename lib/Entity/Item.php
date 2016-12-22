<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 15:51
 */

namespace PbxParser\Entity;

class Item
{
    private $id;

    private $value;

    /**
     * Item constructor.
     *
     * @param $id
     * @param $value
     */
    public function __construct($id, $value) {
        $this->id = $id;
        $this->value = $value;
    }


}