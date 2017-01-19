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
    public function __construct($text, $baseLineNumber) {

        $word = '';
        $isString = false;
        $pre = "";
        $lineNumber = $baseLineNumber;
        $length = mb_strlen($text);

        for ($i = 0; $i < $length; $i++) {
            $char = $text[$i];
            if ($isString) {
                if ($char == '"' && $pre != '\\') {
                    $word .= $char;
                    $this->addWord($word, $lineNumber);
                    $isString = false;
                } else {
                    $word .= $char;
                }
            } else {
                switch ((string)$char) {
                    case " " :
                    case "\t" :
                        $this->addWord($word, $lineNumber);
                        break;
                    case "\n" :
                        $this->addWord($word, $lineNumber);
                        $lineNumber++;
                        break;
                    case ";":
                    case "}":
                    case "{":
                    case "=":
                    case ",":
                    case "(":
                    case ")":
                        $this->addWord($word, $lineNumber);
                        $this->addWord($char, $lineNumber);
                        break;
                    case '"' :
                        $this->addWord($word, $lineNumber);
                        $word .= $char;
                        $isString = true;
                        break;
                    default:
                        $word = $word . $char;
                        break;
                }
            }
            $pre = $char;
        }
    }

    private function addWord(&$word, $lineNumber) {
        if ($word === '') {
            return;
        }

        $this->words[] = $word;
        $this->lineNumbers[count($this->words) - 1] = $lineNumber;
        $word = "";
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