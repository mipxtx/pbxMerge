<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 16:21
 */

namespace PbxParser;

class WordIterator implements \Iterator
{
    private $words = [];

    private $current = 0;

    /**
     * LineIterator constructor.
     *
     * @param string $text
     */
    public function __construct(string $text) {
        $text = str_replace(["\t", "\n"], " ", $text);
        foreach (explode(" ", $text) as $word) {
            if ($word) {
                if ($word[0] == '{') {
                    $this->words[] = '{';
                    $word = ltrim($word, '{');
                    if(!$word){
                        continue;
                    }
                }

                $hasEnd = false;

                foreach ([';', ','] as $char) {
                    if ($word[mb_strlen($word) - 1] == $char) {
                        $word = rtrim($word, $char);
                        if ($word !== '') {
                            $this->words[] = $word;
                        }
                        $this->words[] = $char;
                        $hasEnd = true;
                        break;
                    }
                }
                if(!$hasEnd){
                    $this->words[] = $word;
                }
            }
        }
    }

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
        for ($i = $this->current - 2; $i < $this->current; $i++) {
            echo $this->words[$i] . " ";
        }
        echo "\033[1m" . $this->words[$this->current] . "\033[0m ";

        for ($i = $this->current + 1; $i < $this->current + 3; $i++) {
            echo $this->words[$i] . " ";
        }

        echo "\n";
    }
}