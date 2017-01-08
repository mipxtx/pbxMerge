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
     * @var
     */
    protected $parent;

    /**
     * @return DefineValue
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

    public function setFile(File $file){
        $this->file = $file;
    }

    public function setParent($parent){
        $this->parent = $parent;
    }

    protected function cloneLinks($target){
        $target->setFile($this->file);
        $target->setParent($this->parent);
        return $target;
    }
}