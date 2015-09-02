<?php

/*
 * LegionPE
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
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;
use pocketmine\Server;

class ReloadKitsQuery extends AsyncQuery{
	/** @var int */
	private $uid;
	/** @var int */
	private $class;
	/** @var bool */
	private $msg;
	public function __construct(BasePlugin $main, $uid, $class, $msg = true){
		$this->uid = $uid;
		$this->class = $class;
		$this->msg = $msg;
		parent::__construct($main);
	}
	public function getResultType(){
		return self::TYPE_ALL;
	}
	public function getQuery(){
		return "SELECT kitid, slot, name, value FROM kits_slots WHERE uid=$this->uid AND class=$this->class";
	}
	public function getExpectedColumns(){
		return [
			"kitid" => self::COL_INT,
			"slot" => self::COL_INT,
			"name" => self::COL_STRING,
			"value" => self::COL_INT
		];
	}
	public function onCompletion(Server $server){
		$ses = BasePlugin::getInstance($server)->getSessionByUid($this->uid);
		if(!($ses instanceof Session)){
			return;
		}
		$kitRows = [];
		foreach($this->getResult()["result"] as $resultRow){
			$kitid = (int) $resultRow["kitid"];
			$resultRow["kitid"] = (int) $resultRow["kitid"];
			$resultRow["slot"] = (int) $resultRow["slot"];
			$resultRow["value"] = (int) $resultRow["value"];
			if(!isset($kitRows[$kitid])){
				$kitRows[$kitid] = [$resultRow];
			}else{
				$kitRows[$kitid][] = $resultRow;
			}
		}
		$ses->setLoginDatum("kitrowsarray", $kitRows);
		$ses->reloadKitsCallback();
		if($this->msg){
			$ses->send(Phrases::KITS_RELOADED);
		}
	}
}
