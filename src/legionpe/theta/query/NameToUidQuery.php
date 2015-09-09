<?php

/*
 * Theta
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

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;

class NameToUidQuery extends AsyncQuery{
	private $uid;
	protected $name;
	public function __construct(BasePlugin $main, $name){
		$this->name = $name;
		parent::__construct($main);
	}
	public function getResultType(){
		return self::TYPE_ASSOC;
	}
	public function getQuery(){
		return "SELECT uid FROM users WHERE name={$this->esc($this->name)}";
	}
	public function getExpectedColumns(){
		return [
			"uid" => self::COL_INT
		];
	}
	public function getUid(){
		if(isset($this->uid)){
			return $this->uid;
		}
		return $this->uid = $this->getResult()["result"]["uid"];
	}
}
