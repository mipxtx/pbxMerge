<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 19.01.17
 * Time: 23:42
 */

namespace PbxParser\Tokenizer;

class WordTokenizer extends Tokenizer
{
    /**
     * LineIterator constructor.
     *
     * @param array $text
     * @param $baseLineNumber
     */
    public function __construct(array $text, $baseLineNumber) {
        $this->parseWords($text, $baseLineNumber);
    }

    public function parseWords(array $lines, $baseLineNumber) {
        $lineNumber = $baseLineNumber;
        foreach ($lines as $line) {
            $line = trim($line);
            $isString = false;
            $string = "";
            $words = explode(" ", $line);
            foreach ($words as $word) {
                if ($isString) {
                    $string .= " " . $word;
                    if (strpos($word, '"') !== false) {
                        if (substr_count($word, '"') != substr_count($word, '\\"')) {
                            $this->addOneWord($string, $lineNumber);
                            $isString = false;
                            $string = "";
                        }
                    }
                } else {
                    if ($word[0] == '"') {
                        if (substr_count($word, '"') == 2) {
                            $this->addOneWord($word, $lineNumber);
                        } else {
                            $string = $word;
                            $isString = true;
                        }
                    } else {
                        if ($word[0] == "{") {
                            $this->addOneWord('{', $lineNumber);
                            $word = ltrim($word, "{");
                        }
                        $this->addOneWord($word, $lineNumber);
                    }
                }
            }
            $lineNumber++;
        }
    }

    private function addOneWord($word, $lineNumber) {
        if ($word === '') {
            return;
        }
        $last = $this->getLast($word);
        $this->words[] = $word;
        $this->lineNumbers[count($this->words) - 1] = $lineNumber;

        if ($last) {
            $this->words[] = $last;
            $this->lineNumbers[count($this->words) - 1] = $lineNumber;
        }
    }
    private function getLast(&$word) {
        $last = mb_strlen($word);
        if (in_array($word[$last - 1], [';', ','])) {
            $char = $word[$last - 1];
            $word = rtrim($word, $char);

            return $char;
        }

        return '';
    }
}