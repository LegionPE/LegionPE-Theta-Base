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

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;

class PushChatQuery extends AsyncQuery{
	private $src;
	private $msg;
	private $type;
	private $data;
	public function __construct(BasePlugin $main, $src, $msg, $type, $data = []){
		parent::__construct($main);
		$this->src = $src;
		$this->msg = $msg;
		$this->type = $type;
		$this->data = json_encode($data);
	}
	public function getQuery(){
		return "INSERT INTO chat(src,msg,type,json)VALUES({$this->esc($this->src)},{$this->esc($this->msg)},{$this->esc($this->type)},{$this->esc($this->data)})";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
}
