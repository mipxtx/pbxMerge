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

        $lines = explode("\n", trim(file_get_contents($fileName)));
        $file = new File();
        $block = [];
        $name = "";

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            if (strpos($line, '/* Begin ') === 0) {
                $block = [];
                list($_, $_, $name) = explode(" ", $line);
            } elseif (strpos($line, '/* End ') === 0) {
                $text = implode("\n", $block);
                echo "$name\n$text\n\n";
                $file->addSection($this->parseSection($name, $text));
            } else {
                $block[] = $line;
            }
        }

        return $file;
    }

    /**
     * @param WordIterator $block
     * @return DefineStatements
     */
    public function parseList(WordIterator $block) {
        $list = new DefineStatements();
        // {
        $block->next();
        $this->parseItems($list, $block);
        // }
        $block->next();

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
            $block->debug();
            $item = $this->parseValue($block);

            var_dump($item);
            $result->addItem($item);
            // ;/,
            $block->next();
        }
        $block->next();

        return $result;
    }

    /**
     * @param $name
     * @param string $text
     * @return Section
     */
    public function parseSection($name, $text) {

        $block = new WordIterator($text);
        $section = new Section($name);
        $this->parseItems($section, $block);

        return $section;
    }

    public function parseItems(DefineStatements $container, WordIterator $block) {

        while ($block->current() != "}" && $block->valid()) {
            $item = $this->parseDefine($block);
            $container->addItem($item);
            // ;/,
            $block->next();
        }
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
        $next = $block->getNext();
        $comment = null;
        if ($next == '/*') {
            $comment = $this->parseComment($block);
        }

        return new Value($key, $comment);
    }
}