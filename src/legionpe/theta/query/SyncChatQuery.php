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
use legionpe\theta\utils\FireSyncChatQueryTask;
use pocketmine\Server;

class SyncChatQuery extends AsyncQuery{
	private $main;
	private $class;
	private $task; // TODO
	private $lastId;
	public function __construct(BasePlugin $main, FireSyncChatQueryTask $task){
		$this->class = Settings::$LOCALIZE_CLASS;
		$this->task = $task;
		$task->canFireNext = false;
		$this->lastId = $main->getInternalLastChatId();
		parent::__construct($this->main = $main);
	}
	public function getQuery(){
		return $this->lastId === null ? "SELECT MAX(id)AS id FROM chat" : "SELECT id,unix_timestamp(creation)AS creation,src,msg,type,class,json FROM chat WHERE id>$this->lastId AND (class=0 OR class=$this->class)";
	}
	public function getResultType(){
		return $this->lastId === null ? self::TYPE_ASSOC : self::TYPE_ALL;
	}
	public function getExpectedColumns(){
		return $this->lastId === null ? ["id" => self::COL_INT] : [
			"id" => self::COL_INT,
			"creation" => self::COL_UNIXTIME,
			"src" => self::COL_STRING,
			"msg" => self::COL_STRING,
			"type" => self::COL_INT,
			"class" => self::COL_INT,
			"json" => self::COL_STRING
		];
	}
	public function onCompletion(Server $server){
		$result = $this->getResult();
		if($this->lastId === null){
			$this->main->setInternalLastChatId($result["result"]["id"]);
		}elseif($result["resulttype"] === self::TYPE_ALL){
			$result = $this->getResult()["result"];
			foreach($result as $row){
				$row["json"] = json_decode($row["json"], true);
				$this->main->handleChat($row);
			}
		}
		$this->task->canFireNext = true;
	}
}
