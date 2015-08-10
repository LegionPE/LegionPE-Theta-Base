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

class NewPrivateMessageQuery extends AsyncQuery{
	private $uid;
	private $msg;
	private $args;
	public function __construct(BasePlugin $main, $uid, $msg, array $args = []){
		$this->uid = $uid;
		$this->msg = $msg;
		$this->args = json_encode($args);
		parent::__construct($main);
	}
	public function getQuery(){
		return "INSERT INTO inbox (uid, msg, args) VALUES ($this->uid, {$this->esc($this->msg)}, {$this->esc($this->args)})";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
}
