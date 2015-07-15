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
		return substr($time, 0, -2);
	}
	public static function dir_copy($from, $to){
		$to = rtrim($to, "\\/") . "/";
		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($from)) as $file){
			if(is_file($file)){
				$includePath = ltrim(substr($file, strlen($from)), "\\/");
				$target = $to . $includePath;
				$dir = dirname($target);
				if(!is_dir($dir)){
					mkdir(dirname($target), 0777, true);
				}
				copy($file, $target);
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
				unlink($file);
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
}
