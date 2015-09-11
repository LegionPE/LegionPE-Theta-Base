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

class TeamConfigFetchQuery extends AsyncQuery{
	/** @var int */
	private $tid;
	/** @var int */
	private $bit;
	/** @var int<Session> */
	private $sender;
	/** @var string */
	private $phrase;
	public function __construct(BasePlugin $main, Session $sender, $tid, $bit, $phrase){
		$this->tid = $tid;
		$this->bit = $bit;
		$this->sender = $main->storeObject($sender);
		$this->phrase = $phrase;
		parent::__construct($main);
	}
	public function getResultType(){
		return self::TYPE_ASSOC;
	}
	public function getExpectedColumns(){
		return ["config" => self::COL_INT];
	}
	public function getQuery(){
		return "SELECT config FROM teams WHERE tid=$this->tid";
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		$result = $this->getResult();
		if($result["resulttype"] !== self::TYPE_ASSOC){
			return;
		}
		/** @var Session $sender */
		$sender = $main->fetchObject($this->sender);
		if(!$sender->getPlayer()->isOnline()){
			return;
		}
		$boolean = (bool) ($this->getResult()["result"]);
		if($boolean){
			$sender->send(Phrases::CMD_TEAM_CONFIG_CHECK_TRUE, ["type" => $sender->translate($this->phrase)]);
		}else{
			$sender->send(Phrases::CMD_TEAM_CONFIG_CHECK_FALSE, ["type" => $sender->translate($this->phrase)]);
		}
		return;
	}
}
