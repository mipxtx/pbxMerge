<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 22.12.16
 * Time: 12:09
 */

include __DIR__ . "/vendor/autoload.php";

$string = "		57EB0F8A6E67829443B0D97F41701E9B /* Products */ = {			
			children = (
				AA1762EC93DC071886515F81A6C2821E /* PBXMergeTest.app */,
				E02A4BE40D467283762A61C4A271E34D /* PBXMergeTestTarget2.app */,
			);		
		};
";

echo $string . "\n";
$parser = new \PbxParser\Parser();

$section = $parser->parseSection('text',$string);

print_r($section);

