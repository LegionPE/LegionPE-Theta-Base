<?php

/*
 * LegionPE
 *
 * Copyright (C) 2015 LegendsOfMCPE and contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PEMapModder
 */

namespace legionpe\theta\command\session;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\SessionCommand;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;

class QueryCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "q", "Enable/disable one-to-one direct chat", "/q [target player]", ["query"]);
	}
	protected function run(array $args, Session $sender){
		if(!isset($args[0])){
			if($sender->queryTarget === null){
				return false;
			}
			$sender->setQueryTargetUid(null);
			return $sender->translate(Phrases::CMD_QUERY_CANCELLED);
		}
		$target = $this->getSession($name = array_shift($args));
		if($target === null){
			return $this->notOnline($sender, $name);
		}
		$sender->queryTarget = $target->getUid();
		return $sender->translate(Phrases::CMD_QUERY_SUCCESS, ["target" => $target->getInGameName()]);
	}
}
