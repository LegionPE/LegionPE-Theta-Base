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
use legionpe\theta\Session;
use legionpe\theta\shops\Purchase;
use pocketmine\Server;

class ReloadPurchasesQuery extends AsyncQuery{
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
		return "SELECT pid, id, amplitude, count, expiry FROM purchases WHERE uid=$this->uid AND class=$this->class";
	}
	public function getExpectedColumns(){
		return [
			"pid" => self::COL_INT,
			"id" => self::COL_INT,
			"amplitude" => self::COL_INT,
			"count" => self::COL_INT,
			"expiry" => self::COL_INT,
		];
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		$ses = $main->getSessionByUid($this->uid);
		if($ses instanceof Session){
			$result = $this->getResult();
			if($result["resulttype"] === self::TYPE_ALL){
				$rows = $result["result"];
				$purchases = [];
				foreach($rows as $row){
					$purchases[$row["pid"]] = new Purchase((int) $row["pid"], $this->uid, $this->class, (int) $row["id"], (int) $row["amplitude"], (int) $row["count"], (int) $row["expiry"]);
				}
				$ses->setLoginDatum("purchases", $purchases);
				new ReloadKitsQuery($main, $this->uid, $this->class, $this->msg);
			}
		}
	}
}
