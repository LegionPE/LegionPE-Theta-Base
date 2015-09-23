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
use legionpe\theta\query\TeamRankChangeQuery;
use legionpe\theta\Session;

class TeamRankChangeCommand extends SessionCommand{
	/** @var bool */
	private $promote;
	/** @var string */
	private $humanName;
	/**
	 * @param BasePlugin $main
	 * @param bool $promote
	 */
	public function __construct(BasePlugin $main, $promote){
		$this->promote = $promote;
		$this->humanName = $promote ? "promote" : "demote";
		parent::__construct($main, $name = $promote ? "tprom" : "tdem", ucfirst($this->humanName) . " a team member", "/$name <full name>");
	}
	protected function run(array $args, Session $sender){
		if(!isset($args[0])){
			return false;
		}
		if($sender->getTeamId() === -1){
			return $sender->translate(Phrases::CMD_TEAM_ERR_NOT_IN_TEAM);
		}
		if($sender->getTeamRank() < Settings::TEAM_RANK_SENIOR){
			return $sender->translate(Phrases::CMD_TEAM_RANK_CHANGE_NEED_SENIOR);
		}
		$name = array_shift($args);
		new TeamRankChangeQuery($this->getMain(), $this->promote, $name, $sender);
		return true;
	}
}
