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

class UpdateUserStatusQuery extends AsyncQuery{
	/** @var int */
	private $uid, $status;
	public function __construct(BasePlugin $plugin, $uid, $status){
		$this->uid = $uid;
		$this->status = $status;
		parent::__construct($plugin);
	}
	public function getQuery(){
		return "UPDATE users SET status=$this->status WHERE uid=$this->uid";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
}
