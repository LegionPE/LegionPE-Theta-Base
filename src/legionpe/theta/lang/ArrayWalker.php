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

class ArrayWalker{
	private $keys;
	private $plainList = [];
	public function __construct(array $array){
		$this->keys = [];
		$this->walk($array);
	}
	private function walk(array $array){
		$prefix = implode(".", $this->keys) . ".";
		foreach($array as $k => $v){
			if(is_array($v) and !$this->isLinearArray($v)){
				$cnt = count($this->keys);
				$this->keys[$cnt] = $k;
				$this->walk($v);
				unset($this->keys[$cnt]);
			}else{
				$this->plainList[$prefix . $k] = $v;
			}
		}
	}
	private function isLinearArray(array $array){
		$i = 0;
		foreach($array as $k => $v){
			if($k !== ($i++)){
				return false;
			}
		}
		return true;
	}
	/**
	 * @return string[]|array[]
	 */
	public function getPlainList(){
		return $this->plainList;
	}
}
