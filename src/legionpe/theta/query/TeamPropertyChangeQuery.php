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

class TeamPropertyChangeQuery extends AsyncQuery{
	/** @var int */
	private $sender;
	/** @var int */
	private $tid;
	/** @var string */
	private $column;
	/** @var string */
	private $value;
	/** @var string */
	private $humanPhrase;
	public function __construct(BasePlugin $main, Session $sender, $tid, $column, $value, $humanPhrase){
		parent::__construct($main);
		$this->sender = $main->storeObject($sender);
		$this->tid = $tid;
		$this->column = $column;
		$this->value = $value;
		$this->humanPhrase = $humanPhrase;
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
	public function getQuery(){
		return "UPDATE teams SET $this->column={$this->esc($this->value)} WHERE tid=$this->tid";
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		/** @var Session $sender */
		$sender = $main->fetchObject($this->sender);
		if($sender->getPlayer()->isOnline()){
			$sender->send(Phrases::CMD_TEAM_PROPERTY_VALUE_CHANGED, [
				"type" => $sender->translate($this->humanPhrase),
				"value" => $this->value
			]);
		}
	}
}
