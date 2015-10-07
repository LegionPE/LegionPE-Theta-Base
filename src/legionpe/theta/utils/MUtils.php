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
use pocketmine\utils\TextFormat;

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
	public static function toMd($string){
		if(!\is_array($string)){
			$string = TextFormat::tokenize($string);
		}
		$newString = "";
		$tokens = 0;
		$close = "";
		foreach($string as $token){
			switch($token){
				case TextFormat::BOLD:
					$newString .= $close . "**";
					$close = "**";
					++$tokens;
					break;
				case TextFormat::OBFUSCATED:
					$newString .= $close . "~~";
					$close = "~~";
					++$tokens;
					break;
				case TextFormat::ITALIC:
					$newString .= $close . "_";
					$close = "_";
					++$tokens;
					break;
				case TextFormat::UNDERLINE:
					$newString .= $close;
					$close = "";
					++$tokens;
					break;
				case TextFormat::STRIKETHROUGH:
					$newString .= $close . "~~";
					$close = "~~";
					++$tokens;
					break;
				case TextFormat::RESET:
					$newString .= $close;
					$close = "";
					$tokens = 0;
					break;

				//Colors
				case TextFormat::BLACK:
					$newString .= $close . "`";
					$close = "`";
					++$tokens;
					break;
				case TextFormat::DARK_BLUE:
					$newString .= $close . "`";
					$close = "`";
					++$tokens;
					break;
				case TextFormat::DARK_GREEN:
					$newString .= $close . "`";
					$close = "`";
					++$tokens;
					break;
				case TextFormat::DARK_AQUA:
					$newString .= $close . "`";
					$close = "`";
					++$tokens;
					break;
				case TextFormat::DARK_RED:
					$newString .= $close . "`";
					$close = "`";
					++$tokens;
					break;
				case TextFormat::DARK_PURPLE:
					$newString .= $close . "`";
					$close = "`";
					++$tokens;
					break;
				case TextFormat::GOLD:
					$newString .= $close . "`";
					$close = "`";
					++$tokens;
					break;
				case TextFormat::GRAY:
					$newString .= $close . "`";
					$close = "`";
					++$tokens;
					break;
				case TextFormat::DARK_GRAY:
					$newString .= $close . "`";
					$close = "`";
					++$tokens;
					break;
				case TextFormat::BLUE:
					$newString .= $close . "`";
					$close = "`";
					++$tokens;
					break;
				case TextFormat::GREEN:
					$newString .= $close . "`";
					$close = "`";
					++$tokens;
					break;
				case TextFormat::AQUA:
					$newString .= $close . "`";
					$close = "`";
					++$tokens;
					break;
				case TextFormat::RED:
					$newString .= $close . "`";
					$close = "`";
					++$tokens;
					break;
				case TextFormat::LIGHT_PURPLE:
					$newString .= $close . "`";
					$close = "`";
					++$tokens;
					break;
				case TextFormat::YELLOW:
					$newString .= $close . "`";
					$close = "`";
					break;
				case TextFormat::WHITE:
					$newString .= $close;
					$close = "";
					++$tokens;
					break;
				default:
					$newString .= $token;
					break;
			}
		}

		return $newString;
	}

	private static $characterWidths = [
		4, 2, 5, 6, 6, 6, 6, 3, 5, 5, 5, 6, 2, 6, 2, 6,
		6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 2, 2, 5, 6, 5, 6,
		7, 6, 6, 6, 6, 6, 6, 6, 6, 4, 6, 6, 6, 6, 6, 6,
		6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 4, 6, 4, 6, 6,
		6, 6, 6, 6, 6, 5, 6, 6, 2, 6, 5, 3, 6, 6, 6, 6,
		6, 6, 6, 4, 6, 6, 6, 6, 6, 6, 5, 2, 5, 7
	];

	const CHAT_WINDOW_WIDTH = 240;
	const CHAT_STRING_LENGTH = 119;
	const ALIGN_LEFT = 0;
	const ALIGN_CENTER = 1;
	const ALIGN_RIGHT = 2;

	private static $allowedChars = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_abcdefghijklmnopqrstuvwxyz{|}~";

	private static $allowedCharsArray = [];

	public static function init(){
		self::$allowedCharsArray = [];
		$len = strlen(self::$allowedChars);
		for($i = 0; $i < $len; ++$i){
			self::$allowedCharsArray[self::$allowedChars{$i}] = self::$characterWidths[$i];
		}
	}
	public static function wrap($text){
		$result = "";
		$len = strlen($text);
		$lineWidth = 0;
		$lineLength = 0;

		for($i = 0; $i < $len; ++$i){
			$char = $text{$i};

			if($char === "\n"){
				$lineLength = 0;
				$lineWidth = 0;
			}elseif(isset(self::$allowedCharsArray[$char])){
				$width = self::$allowedCharsArray[$char];

				if($lineLength + 1 > self::CHAT_STRING_LENGTH or $lineWidth + $width > self::CHAT_WINDOW_WIDTH){
					$result .= "\n";
					$lineLength = 0;
					$lineWidth = 0;
				}

				++$lineLength;
				$lineWidth += $width;
			}else{
				return $text;
			}

			$result .= $char;
		}

		return $result;
	}
	public static function getLength($text){
		$length = 0;
		for($i = 0; $i < strlen($text); $i++){
			$char = $text{$i};
			if(isset(self::$allowedCharsArray[$char])){
				$length += self::$allowedCharsArray[$char];
			}else{
				$length += 8;
			}
		}
		return $length;
	}
	/**
	 * @param string $text
	 * @param string $char
	 * @param int $mode
	 * @param bool $array
	 * @return array|string
	 */
	public static function align($text, $char = " ", $mode = self::ALIGN_CENTER, $array = false){
		$lengths = [];
		$lines = explode("\n", $text);
		foreach($lines as $i => $line){
			$lengths[$i] = self::getLength($line);
		}
		$paddingLength = self::getLength($char);
		foreach($lines as $i => &$line){
			$deficit = $lengths[$i];
			$need = round($deficit / $paddingLength);
			if($mode === self::ALIGN_LEFT){
				$line .= str_repeat(" ", $need);
			}elseif($mode === self::ALIGN_RIGHT){
				$line = str_repeat(" ", $need) . $line;
			}else{
				$need /= 2;
				$line = str_repeat(" ", (int) $need) . $line . str_repeat(" ", ceil($need));
			}
		}
		return $array ? $lines : implode("\n", $lines);
	}
}

MUtils::init();
