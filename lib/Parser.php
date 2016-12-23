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
use PbxParser\Entity\DefineStatements;
use PbxParser\Entity\File;
use PbxParser\Entity\Section;
use PbxParser\Entity\Value;

class Parser
{
    public function parse($fileName) {

        $text = file_get_contents($fileName);
        $lines = explode("\n", trim($text));
        $head = array_shift($lines);
        $block = new WordIterator(implode("\n", $lines), 2);
        $file = new File($head);
        $this->parseItems($file, $block);

        return $file;
    }

    /**
     * @param WordIterator $block
     * @return DefineStatements
     */
    public function parseList(WordIterator $block) {
        $list = new DefineStatements();
        $this->parseItems($list, $block);

        return $list;
    }

    /**
     * @param WordIterator $block
     * @return ValueArray
     */
    public function parseArray(WordIterator $block) {

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

    public function parseItems(DefineStatements $container, WordIterator $block) {
        // {
        $block->next();

        /** @var Section $currentSection */
        $currentSection = null;
        while ($block->current() != "}") {
            if ($block->current() == "/*") {
                $coment = $this->parseComment($block);
                if (preg_match('/Begin ([A-Za-z]+) section/', $coment, $out)) {
                    $currentSection = new Section($out[1]);
                    $container->addItem($currentSection);
                } elseif (preg_match('/End ([A-Za-z]+) section/', $coment, $out)) {
                    $currentSection = null;
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

    public function parseComment(WordIterator $block) {
        $text = [];
        $block->next();
        do {
            $text[] = $block->current();
        } while ($block->getNext() != '*/');

        $block->next();

        return implode(" ", $text);
    }

    /**
     * @param WordIterator $block
     * @return Define
     * @throws Exception
     */
    public function parseDefine(WordIterator $block) {
        $key = $this->parseValue($block);
        if ($block->current() != "=") {
            var_dump($key);
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

        return new Define($key, $value);
    }

    /**
     * @param WordIterator $block
     * @return Value
     */
    public function parseValue(WordIterator $block) {
        $key = $block->current();

        if ($key[0] == '"' && $key != '""') {
            do {
                $current = $block->getNext();
                $key .= " " . $current;
            } while (strpos($current, '"') === false);
        }

        $next = $block->getNext();
        $comment = null;
        if ($next == '/*') {
            $comment = $this->parseComment($block);
        }

        return new Value($key, $comment);
    }
}