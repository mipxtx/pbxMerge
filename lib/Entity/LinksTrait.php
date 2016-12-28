<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 26.12.16
 * Time: 14:08
 */

namespace PbxParser\Entity;

trait LinksTrait
{
    /**
     * @var File
     */
    protected $file;

    /**
     * @var DefineStatements
     */
    protected $parent;

    /**
     * @return DefineStatements
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @return File
     */
    public function getFile() {
        return $this->file;
    }

    public function initLinks(DefineValue $parent){
        $this->parent = $parent;
        $this->file = $parent->getFile();
        foreach($this->getChildren() as $child){
            $child->initLinks($this);
        }
    }
}