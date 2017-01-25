<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 21.12.16
 * Time: 22:34
 */

namespace PbxParser;

use PbxParser\Entity\Define;
use PbxParser\Entity\ValueArray;
use PbxParser\Entity\Dictionary;
use PbxParser\Entity\File;
use PbxParser\Entity\Section;
use PbxParser\Entity\Value;
use PbxParser\Tokenizer\PhpTokenizer;
use PbxParser\Tokenizer\SymbolTokenizer;
use PbxParser\Tokenizer\Tokenizer;
use PbxParser\Tokenizer\WordTokenizer;

class Parser
{
    private $type;

    /**
     * Parser constructor.
     *
     * @param $type
     */
    public function __construct($type = 'php') {
        $this->type = $type;
    }

    public function parse($fileName) {
        $text = file_get_contents($fileName);

        return $this->parseString($text, $fileName);
    }

    public function parseString($text, $fileName = "") {
        $lines = explode("\n", trim($text));
        $head = array_shift($lines);
        switch ($this->type) {
            case 'symbol':
                $block = new SymbolTokenizer(implode("\n", $lines), 2);
                break;
            case 'word' :
                $block = new WordTokenizer($lines, 2);
                break;
            case 'php':
                $block = new PhpTokenizer(implode("\n", $lines), 1);
                break;
            default:
                throw new \Exception('tokenizer not found');
        }

        $file = new File($head, $fileName);
        $this->parseItems($file, $block);
        $file->initLinks($file);

        return $file;
    }

    /**
     * @param Tokenizer $block
     * @return Dictionary
     */
    public function parseList(Tokenizer $block) {
        $list = new Dictionary();
        $this->parseItems($list, $block);

        return $list;
    }

    /**
     * @param Tokenizer $block
     * @return ValueArray
     */
    public function parseArray(Tokenizer $block) {

        $result = new ValueArray();
        $block->next();
        while ($block->current() != ")") {
            $item = $this->parseValue($block);
            $result->addItem($item);
            // ;/,
            $block->next();
        }
        $block->next();

        return $result;
    }

    public function parseItems(Dictionary $container, Tokenizer $block) {
        // {
        $block->next();

        /** @var Section $currentSection */
        $currentSection = null;
        while ($block->current() != "}" && $block->valid()) {
            if (strpos($block->current(), "/*") === 0) {
                $coment = $this->parseComment($block);
                if (preg_match('/Begin ([A-Za-z]+) section/', $coment, $out)) {
                    $currentSection = new Section($out[1]);
                    $container->addItem($currentSection);
                } elseif (preg_match('/End ([A-Za-z]+) section/', $coment, $out)) {
                    $currentSection = null;
                }else{
                    // ;/,
                    $block->next();
                }
            } else {
                $item = $this->parseDefine($block);
                if ($currentSection == null) {
                    $container->addItem($item);
                } else {
                    $currentSection->addItem($item);
                }
                // ;/,
                $block->next();
            }

        }
        // }
        $block->next();
    }

    public function parseComment(Tokenizer $block) {

        $word = $block->current();
        $text = [$word];

        while (strpos($word, '*/') === false) {
            $word = $block->getNext();
            $text[] = $word;
        }

        $block->next();

        return implode(" ", $text);
    }

    /**
     * @param Tokenizer $block
     * @return Define
     * @throws Exception
     */
    public function parseDefine(Tokenizer $block) {

        $key = $this->parseValue($block);

        if ($block->current() != "=") {
            $block->debug();
            throw new Exception('eq not found');
        }
        $block->next();

        if ($block->current() == '{') {
            $value = $this->parseList($block);
        } elseif ($block->current() == '(') {
            $value = $this->parseArray($block);
        } else {
            $value = $this->parseValue($block);
        }

        $define = new Define($key, $value);

        return $define;
    }

    /**
     * @param Tokenizer $block
     * @return Value
     */
    public function parseValue(Tokenizer $block) {
        $key = $block->current();

        if (substr_count($key, '"') - substr_count($key, '\"') == 1) {
            do {
                $current = $block->getNext();
                $key .= " " . $current;
            } while (strpos($current, '"') === false);
        }

        $next = $block->getNext();

        $comment = null;
        if (strpos($next, '/*') === 0) {
            $comment = $this->parseComment($block);

        }
        $ret = new Value($key, $comment);

        return $ret;
    }
}