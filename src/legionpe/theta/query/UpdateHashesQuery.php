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

class UpdateHashesQuery extends AsyncQuery{
	/** @var int */
	private $uid;
	/** @var string */
	private $new;
	public function __construct(BasePlugin $main, $uid, $new){
		$this->uid = $uid;
		$this->new = $new;
		parent::__construct($main);
	}
	public function getQuery(){
		return "UPDATE users SET hash={$this->esc($this->new, true)}, oldhash='' WHERE uid=$this->uid";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
	public function reportDebug(){
		return false;
	}
}
