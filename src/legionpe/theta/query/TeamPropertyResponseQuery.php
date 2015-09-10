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

class TeamPropertyResponseQuery extends AsyncQuery{
	private $sender;
	/** @var int */
	private $tid;
	/** @var string */
	private $column;
	/** @var string */
	private $humanPhrase;
	public function __construct(BasePlugin $main, Session $sender, $tid, $column, $humanPhrase){
		$this->sender = $main->storeObject($sender);
		$this->tid = $tid;
		$this->column = $column;
		$this->humanPhrase = $humanPhrase;
		parent::__construct($main);
	}
	public function getResultType(){
		return self::TYPE_ASSOC;
	}
	public function getQuery(){
		return "SELECT $this->column FROM teams WHERE tid=$this->tid";
	}
	public function onCompletion(Server $server){
		$result = $this->getResult();
		if($result["resulttype"] === self::TYPE_ASSOC){
			$main = BasePlugin::getInstance($server);
			/** @var Session $sender */
			$sender = $main->fetchObject($this->sender);
			if($sender->getPlayer()->isOnline()){
				$sender->send(Phrases::CMD_TEAM_PROPERTY_CHECK, [
					"type" => $sender->translate($this->humanPhrase),
					"value" => $result["result"][$this->column]
				]);
			}
		}
	}
}
