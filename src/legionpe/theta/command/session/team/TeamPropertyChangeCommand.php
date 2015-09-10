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

namespace legionpe\theta\command\session\team;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\SessionCommand;
use legionpe\theta\config\Settings;
use legionpe\theta\lang\Phrases;
use legionpe\theta\query\TeamPropertyChangeQuery;
use legionpe\theta\query\TeamPropertyResponseQuery;
use legionpe\theta\Session;

/**
 * Class TeamPropertyChangeCommand - command to get/set string team details.
 * @package legionpe\theta\command\session\team
 */
class TeamPropertyChangeCommand extends SessionCommand{
	/** @var string */
	private $humanPhrase, $column;
	public function __construct(BasePlugin $main, $aliases, $humanPhrase, $column){
		$this->humanPhrase = $humanPhrase;
		$this->column = $column;
		$humanName = $this->getPlugin()->getLanguageManager()->get($humanPhrase, []);
		parent::__construct($main, $cmd = array_shift($aliases), "Change your team $humanName", "/$cmd [new $humanName...] (use `|` to separate lines)", $aliases);
	}
	protected function run(array $args, Session $sender){
		if($sender->getTeamId() === -1){
			return $sender->translate(Phrases::CMD_TEAM_ERR_NOT_IN_TEAM);
		}
		if(!isset($args[0])){
			new TeamPropertyResponseQuery($this->getPlugin(), $sender, $sender->getTeamId(), $this->column, $this->humanPhrase);
			return true;
		}
		if($sender->getTeamRank() >= Settings::TEAM_RANK_COLEAD){
			return $sender->translate(Phrases::CMD_TEAM_PROPERTY_NEED_CO_LEADER, [
				"type" => $sender->translate($this->humanPhrase)
			]);
		}
		$text = preg_replace('/[ \t]+\|[ \t]+/', "\n", implode(" ", $args));
		$text = trim($text);
		new TeamPropertyChangeQuery($this->getPlugin(), $sender, $sender->getTeamId(), $this->column, $text, $this->humanPhrase);
		return true;
	}
}
