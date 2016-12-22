<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 22:34
 */

namespace PbxParser\Entity;

class File
{
    private $sections = [];

    public function addSection(Section $section) {
        $this->sections[] = $section;
    }

    /**
     * @return Section[]
     */
    public function getSections(): array {
        return $this->sections;
    }


}