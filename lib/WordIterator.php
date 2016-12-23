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

    private $lineNumbers = [];

    private $current = 0;

    /**
     * LineIterator constructor.
     *
     * @param string $text
     * @param $baseLineNumber
     */
    public function __construct(string $text, $baseLineNumber) {
        $text = str_replace(["\t"], " ", $text);
        $lineNumber = $baseLineNumber;
        foreach (explode(" ", $text) as $word) {
            if ($word) {
                $lines = explode("\n", $word);
                $first = array_shift($lines);
                $this->addWord($first, $lineNumber);
                foreach ($lines as $line) {
                    $lineNumber++;
                    $this->addWord($line, $lineNumber);
                }
            }
        }
    }

    private function addWord($word, $lineNumber) {
        if (!$word) {
            return;
        }

        if ($word[0] == '{') {
            $this->appendWord('{', $lineNumber);
            $word = ltrim($word, '{');
            if (!$word) {
                return;
            }
        }

        $hasEnd = false;

        foreach ([';', ','] as $char) {
            if ($word[mb_strlen($word) - 1] == $char) {
                $word = rtrim($word, $char);
                if ($word !== '') {
                    $this->appendWord($word, $lineNumber);
                }
                $this->appendWord($char, $lineNumber);
                $hasEnd = true;
                break;
            }
        }
        if (!$hasEnd) {
            $this->appendWord($word, $lineNumber);
        }
    }

    private function appendWord($word, $lineNumber) {
        $this->words[] = $word;
        $this->lineNumbers[count($this->words)-1] = $lineNumber;
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
}