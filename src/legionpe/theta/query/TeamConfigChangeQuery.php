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

class TeamConfigChangeQuery extends AsyncQuery{
	/** @var int */
	private $sender;
	/** @var int */
	private $tid;
	/** @var string */
	private $bit;
	/** @var string */
	private $boolean;
	/** @var string */
	private $humanPhrase;
	public function __construct(BasePlugin $main, Session $sender, $tid, $bit, $boolean, $humanPhrase){
		parent::__construct($main);
		$this->sender = $main->storeObject($sender);
		$this->tid = $tid;
		$this->bit = $bit;
		$this->boolean = $boolean;
		$this->humanPhrase = $humanPhrase;
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
	public function getQuery(){
		return $this->boolean ?
			"UPDATE teams SET config = config | $this->bit WHERE tid=$this->tid" :
			"UPDATE teams SET config=config & ~$this->bit WHERE tid=$this->tid";
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		/** @var Session $sender */
		$sender = $main->fetchObject($this->sender);
		if($sender->getPlayer()->isOnline()){
			$sender->send($this->boolean ? Phrases::CMD_TEAM_CONFIG_VALUE_CHANGED_TRUE : Phrases::CMD_TEAM_CONFIG_VALUE_CHANGED_FALSE, [
				"type" => $sender->translate($this->humanPhrase),
			]);
		}
	}
}
