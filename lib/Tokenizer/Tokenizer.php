<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 16:21
 */

namespace PbxParser\Tokenizer;

abstract class Tokenizer implements \Iterator
{
    protected $words = [];

    protected $lineNumbers = [];

    protected $current = 0;

    public function next() {
        $this->current++;
    }

    public function current() {
        return $this->words[$this->current];
    }

    public function key() {
        return $this->current;
    }

    public function total() {
        return count($this->words);
    }

    public function valid() {
        return array_key_exists($this->current, $this->words);
    }

    public function rewind() {
        $this->current = 0;
    }

    public function getNext() {
        $this->next();

        return $this->current();
    }

    public function debug() {
        echo 'at line: ' . $this->lineNumbers[$this->current] . "\n";

        for ($i = $this->current - 2; $i < $this->current; $i++) {
            echo $this->words[$i] . " ";
        }
        echo "\033[1m" . $this->words[$this->current] . "\033[0m ";

        for ($i = $this->current + 1; $i < $this->current + 3; $i++) {
            echo $this->words[$i] . " ";
        }

        echo "\n";
    }

    public function getWords(){
        return $this->words;
    }
}