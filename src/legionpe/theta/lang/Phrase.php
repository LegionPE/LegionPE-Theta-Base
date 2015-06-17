<?php

/**
 * Theta
 * Copyright (C) 2015 PEMapModder
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace legionpe\theta\lang;

class Phrase{
	private $name;
	private $impls = [];
	public function __construct($name){
		$this->name = $name;
	}
	public function setImplementation($lang, $impl){
		foreach((new \ReflectionClass(Phrases::class))->getConstants() as $name => $value){
			if(substr($name, 0, 4) === "VAR_"){
				$impl = str_replace("%" . substr($name, 4) . "%", $value, $impl);
			}
		}
		$this->impls[$lang] = $impl;
	}
	public function get(array $vars, array $fallbackList){
		foreach($fallbackList as $lang){
			if(isset($this->impls[$lang])){
				return str_replace(array_map(function($name){
					return "%$name%";
				}, array_keys($vars)), array_values($vars), $this->impls[$lang]);
			}
		}
		return $this->name;
	}
	public function getImplementations(){
		return $this->impls;
	}
}
