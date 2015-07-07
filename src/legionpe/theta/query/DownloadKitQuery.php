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
use legionpe\theta\config\Settings;
use pocketmine\Server;

class DownloadKitQuery extends AsyncQuery{
	/** @var int */
	public $uid;
	/** @var int */
	public $kitid;
	public $rows;
	/** @var int */
	private $class;
	public function __construct(BasePlugin $main, $uid, $kitid){
		parent::__construct($main);
		$this->uid = $uid;
		$this->kitid = $kitid;
		$this->class = Settings::$LOCALIZE_CLASS;
	}
	public function getQuery(){
		return "SELECT slot,name,value FROM kits_slots WHERE uid=$this->uid AND kitid=$this->kitid AND class=$this->class";
	}
	public function getResultType(){
		return self::TYPE_ALL;
	}
	public function getExpectedColumns(){
		return [
			"slot" => self::COL_INT,
			"name" => self::COL_STRING,
			"value" => self::COL_INT
		];
	}
	public function onCompletion(Server $server){
		$result = $this->getResult();
		$this->rows = $result["result"];
	}
}
