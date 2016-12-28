<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 23:38
 */

namespace PbxParser\Entity;

interface DefineValue
{
    /**
     * @return DefineValue
     */
    public function getParent();

    /**
     * @return File
     */
    public function getFile();

    /**
     * @return string
     */
    public function getPath();

    /**
     * @param DefineValue $val
     * @return bool
     */
    public function equal(DefineValue $val);

    /**
     * @return DefineValue[]
     */
    public function getChildren();

    /**
     * @param DefineValue $parent
     * @return void
     */
    public function initLinks(DefineValue $parent);
}