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
	/** @var bool */
	private $boolean;
	public function __construct(BasePlugin $main, $bit, $boolean, $humanPhrase, array $aliases){
		$this->bit = $bit;
		$this->humanPhrase = $humanPhrase;
		$humanName = $main->getLanguageManager()->get($humanPhrase, []);
		parent::__construct($main, $cmd = array_shift($aliases), "Toggle whether your team is $humanName", "/$cmd [on|off]", $aliases);
		$this->boolean = $boolean;
	}
	protected function run(array $args, Session $sender){
		if($sender->getTeamId() === -1){
			return $sender->translate(Phrases::CMD_TEAM_ERR_NOT_IN_TEAM);
		}
		if($sender->getTeamId() < Settings::TEAM_RANK_COLEAD){
			return $sender->translate(Phrases::CMD_TEAM_CONFIG_NEED_CO_LEADER, ["type" => $sender->translate($this->humanPhrase)]);
		}
		new TeamConfigChangeQuery($this->getPlugin(), $sender, $sender->getTeamId(), $this->bit, $this->boolean, $this->humanPhrase);
		return true;
	}
}
