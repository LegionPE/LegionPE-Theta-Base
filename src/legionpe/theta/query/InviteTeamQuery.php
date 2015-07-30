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

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;

class InviteTeamQuery extends AsyncQuery{
	private $tid;
	private $name;
	public function __construct(BasePlugin $main, $tid, $name){
		$this->tid = $tid;
		$this->name = $name;
		parent::__construct($main);
	}
	public function onPreQuery(\mysqli $db){
		// TODO
	}
	public function getQuery(){
	}
	public function getResultType(){
		// TODO: Implement getResultType() method.
	}
}
