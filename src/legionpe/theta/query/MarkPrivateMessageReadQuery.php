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

class MarkPrivateMessageReadQuery extends AsyncQuery{
	private $msgid = [];
	public function __construct(BasePlugin $main, array $msgid){
		$this->msgid = $msgid;
		parent::__construct($main);
	}
	public function getQuery(){
		$condition = implode(" OR ", array_map(function ($msgid){
			return "msgid=$msgid";
		}, $this->msgid));
		return "UPDATE inbox SET status=" . SaveSinglePlayerQuery::INBOX_READ . " WHERE $condition";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
}
