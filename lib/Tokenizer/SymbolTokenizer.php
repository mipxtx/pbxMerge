<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 19.01.17
 * Time: 23:41
 */

namespace PbxParser\Tokenizer;

class SymbolTokenizer extends Tokenizer
{

    /**
     * LineIterator constructor.
     *
     * @param string $text
     * @param $baseLineNumber
     */
    public function __construct(string $text, $baseLineNumber) {
        $this->parseSymbols($text, $baseLineNumber);
    }

    private function parseSymbols(string $text, $baseLineNumber) {
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
}