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
use legionpe\theta\query\TeamConfigChangeQuery;
use legionpe\theta\query\TeamConfigFetchQuery;
use legionpe\theta\Session;

/**
 * Class TeamConfigCommand - command to get/set boolean team details.
 * @package legionpe\theta\command\session\team
 */
class TeamConfigCommand extends SessionCommand{
	/** @var int */
	private $bit;
	/** @var string */
	private $humanPhrase;
	public function __construct(BasePlugin $main, $bit, $boolean, $humanPhrase, array $aliases){
		$this->bit = $bit;
		$this->humanPhrase = $humanPhrase;
		$humanName = $this->getPlugin()->getLanguageManager()->get($humanPhrase, []);
		parent::__construct($main, $cmd = array_shift($aliases), "Toggle whether your team is $humanName", "/$cmd [on|off]", $aliases);
	}
	protected function run(array $args, Session $sender){
		if($sender->getTeamId() === -1){
			return $sender->translate(Phrases::CMD_TEAM_ERR_NOT_IN_TEAM);
		}
		if(isset($args[0])){
			$arg = array_shift($args);
			if($arg === "on"){
				$boolean = true;
			}elseif($arg === "off"){
				$boolean = false;
			}
		}
		if(!isset($boolean)){
			new TeamConfigFetchQuery($this->getPlugin(), $sender, $sender->getTeamId(), $this->bit, $this->humanPhrase);
			return true;
		}
		if($sender->getTeamId() < Settings::TEAM_RANK_COLEAD){
			return $sender->translate(Phrases::CMD_TEAM_CONFIG_NEED_CO_LEADER, ["type" => $sender->translate($this->humanPhrase)]);
		}
		new TeamConfigChangeQuery($this->getPlugin(), $sender, $sender->getTeamId(), $this->bit, $boolean, $this->humanPhrase);
		return true;
	}
}
