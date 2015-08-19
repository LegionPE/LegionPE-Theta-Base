<?php

/*
 * LegionPE Theta
 *
 * Copyright (C) 2015 PEMapModder and contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PEMapModder
 */

namespace legionpe\theta\utils;

use pocketmine\entity\Projectile;
use pocketmine\math\Vector3;

class MUtils{
	public static function word_quantitize(&$word, $count){
		if($count > 1){ // not >= 2
			self::word_toPlural($word);
			$word = "$count $word";
		}elseif(substr($word, -1) !== "s"){
			self::word_addSingularArticle($word);
		}
	}
	public static function word_toPlural(&$word){
		if(in_array(substr($word, -1), str_split("osxz"))){
			$word .= "es";
		}elseif(in_array(substr($word, -2), ["sh", "ch"])){
			$word .= "es";
		}elseif(substr($word, -1) === "y"){
			$word = substr($word, 0, -1) . "ies";
		}elseif(substr($word, -1) === "f"){
			$word = substr($word, 0, -1) . "ves";
		}else{
			$word .= "s";
		}
	}
	public static function word_addSingularArticle(&$word){
		$word = (self::word_startsWithVowel($word) ? "an" : "a") . " $word";
	}
	public static function word_startsWithVowel($word){
		return in_array(strtolower(substr($word, 0, 1)), str_split("aeiou"));
	}
	public static function word_camelToStd(&$word){
		$word = preg_replace_callback('/(.)([A-Z])/', function ($match){
			return $match[1] . strtolower($match[2]);
		}, $word);
	}
	public static function num_getOrdinal($num){
		$rounded = $num % 100;
		if(3 < $rounded and $rounded < 21){
			return "th";
		}
		$unit = $rounded % 10;
		if($unit === 1){
			return "st";
		}
		if($unit === 2){
			return "nd";
		}
		return $unit === 3 ? "rd" : "th";
	}
	public static function num_forceSign($num){
		if($num > 0){
			return "+$num";
		}
		if($num < 0){
			return "$num";
		}
		return "0";
	}
	public static function time_secsToString($secs){
		if($secs === 0){
			return "0 s";
		}
		$hours = 0;
		$minutes = 0;
		while($secs >= 3600){
			$hours++;
			$secs -= 3600;
		}
		while($secs >= 60){
			$minutes++;
			$secs -= 60;
		}
		$time = "";
		if($hours > 0){
			$time .= "$hours hr ";
		}
		if($minutes > 0){
			$time .= "$minutes min ";
		}
		if($secs > 0){
			$time .= "$secs s ";
		}
		return substr($time, 0, -1);
	}
	public static function dir_copy($from, $to){
		$to = rtrim($to, "\\/") . "/";
		/** @var \SplFileInfo $file */
		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($from)) as $file){
			if($file->isFile()){
				$target = $to . ltrim(substr($file->getRealPath(), strlen($from)), "\\/");
				$dir = dirname($target);
				if(!is_dir($dir)){
					mkdir(dirname($target), 0777, true);
				}
				copy($file->getRealPath(), $target);
			}
		}
	}
	public static function dir_delete($dir){
		$dir = rtrim($dir, "/\\") . "/";
		foreach(scandir($dir) as $file){
			if($file === "." or $file === ".."){
				continue;
			}
			$path = $dir . $file;
			if(is_dir($path)){
				self::dir_delete($path);
			}else{
				unlink($path);
			}
		}
		rmdir($dir);
	}
	public static function fillArray(array &$array, $size, $value, $clone = true){
		for($i = 0; $i < $size; $i++){
			if(!isset($array[$i])){
				$array[$i] = (is_object($value) and $clone) ? (clone $value) : $value;
			}
		}
	}
	public static function getArrowCollisionBlock(Projectile $p){
		if($p->isCollidedVertically){
			$block = $p->getLevel()->getBlock($p->floor());
			if($block->getId() === 0){
				$block = $p->getLevel()->getBlock($p->floor()->add(0, 1));
			}
			if($block->getId() === 0){
				$block = $p->getLevel()->getBlock($p->floor()->subtract(1));
			}
			return $block->getId() === 0 ? null : $block;
		}
		if($p->isCollidedHorizontally){
			$floor = $p->floor();
			$pos = new Vector3(($p->x - $floor->x >= 0.5) ? ($floor->x + 1) : ($floor->x - 1), $floor->y, ($p->z - $floor->z >= 0.5) ? ($floor->z + 1) : ($floor->z - 1));
			$block = $p->getLevel()->getBlock($pos);
			return $block->getId() === 0 ? null : $block;
		}
		return null;
	}
	/**
	 * @param int $integer
	 * @return string
	 * @link http://stackoverflow.com/questions/14994941/numbers-to-roman-numbers-with-php
	 */
	public static function romanic_number($integer){
		if($integer === 0){
			return "O";
		}
		$table = ["M" => 1000, "CM" => 900, "D" => 500, "CD" => 400, "C" => 100, "XC" => 90, "L" => 50, "XL" => 40, "X" => 10, "IX" => 9, "V" => 5, "IV" => 4, "I" => 1];
		$return = "";
		if($integer < 0){
			$return = "-";
			$integer *= -1;
		}
		while($integer > 0){
			foreach($table as $rom => $arb){
				if($integer >= $arb){
					$integer -= $arb;
					$return .= $rom;
					break;
				}
			}
		}
		return $return;
	}
}
