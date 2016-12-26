<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 26.12.16
 * Time: 11:51
 */

namespace PbxParser\Entity;

class MergeService
{
    /**
     * @param File[] $files
     */
    public function merge(array $files){
        $out = new File($files[0]->getHeading());

        foreach($files as $file){

        }

    }

    /**
     * @param DefineValue[] $defs
     */
    private function mergeDefines(array $defs){

    }

}