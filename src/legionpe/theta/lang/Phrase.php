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

namespace legionpe\theta\lang;

class Phrase{
	private $name;
	private $values = [];
	public function __construct($name){
		$this->name = $name;
	}
	public function setImplementation($lang, $impl){
		if(is_string($impl)){
			foreach((new \ReflectionClass(Phrases::class))->getConstants() as $name => $value){
				if(substr($name, 0, 4) === "VAR_"){
					$impl = str_replace("%" . substr($name, 4) . "%", $value, $impl);
				}
			}
		}
		$this->values[$lang] = $impl;
	}
	/**
	 * @param array $vars
	 * @param array $fallbackList
	 * @return string|mixed
	 */
	public function get(array $vars, array $fallbackList){
		foreach($fallbackList as $lang){
			if(isset($this->values[$lang])){
				$impl = $this->values[$lang];
				return is_string($impl) ?
					str_replace(array_map(function ($name){
						return "%$name%";
					}, array_keys($vars)), array_values($vars), $impl) :
					$impl;
			}
		}
		return $this->name;
	}
	public function getImplementations(){
		return $this->values;
	}
}
