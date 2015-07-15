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
		$this->uid = $uid;
		$this->kitid = $kitid;
		$this->class = Settings::$LOCALIZE_CLASS;
		parent::__construct($main);
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
