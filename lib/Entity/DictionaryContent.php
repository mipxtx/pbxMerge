<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 26.12.16
 * Time: 12:05
 */

namespace PbxParser\Entity;

interface DictionaryContent extends DefineValue
{
    public function getName(): string;
}