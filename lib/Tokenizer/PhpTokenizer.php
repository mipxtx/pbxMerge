<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 19.01.17
 * Time: 23:49
 */

namespace PbxParser\Tokenizer;

class PhpTokenizer extends Tokenizer
{
    private $symbols = ['{', '}', '(', ')', ';', ',', '='];

    public function __construct($string, $base) {
        $tokens = token_get_all("<?php " . $string);
        array_shift($tokens);
        $word = "";
        $line = $base;
        $string = "";

        foreach ($tokens as $i => $token) {

            if ($string) {
                if (is_array($token)) {
                    $string .= $token[1];
                    $line = $token[2] + $base;
                } else {
                    $string .= $token;
                    if ($token == '"') {
                        $this->addWord($string, $line);
                        $string = "";
                    }
                }
                continue;
            }

            if (is_array($token)) {
                if ($token[0] == T_WHITESPACE) {
                    $this->addWord($word, $line);
                    $word = "";
                    continue;
                }
                $line = $token[2] + $base;
                $value = $token[1];
            } else {
                if ($token == '"') {
                    $string = $token;
                    continue;
                }
                $value = $token;
            }

            if (in_array($value, $this->symbols)) {
                $this->addWord($word, $line);
                $this->addWord($value, $line);
                $word = "";
            } else {
                $word .= $value;
            }
        }
    }

    public function addWord($word, $lineNumber) {
        if ($word !== '') {
            $this->words[] = $word;
            $this->lineNumbers[] = $lineNumber;
        }
    }
}