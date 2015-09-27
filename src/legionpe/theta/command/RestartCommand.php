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

namespace legionpe\theta\command;

use legionpe\theta\BasePlugin;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;
use legionpe\theta\utils\MUtils;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class RestartCommand extends ThetaCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "restart", "Check server restart time", "/restart", ["rst"]);
	}
	public function execute(CommandSender $sender, $commandLabel, array $args){
		$leftTicks = $this->getMain()->getServer()->getTick() - $this->getMain()->getRestartTime();
		$leftSecs = $leftTicks / 20;
		$string = MUtils::time_secsToString($leftSecs);
		if($sender instanceof Player and ($ses = $this->getSession($sender)) instanceof Session){
			$ses->translate(Phrases::CMD_RESTART_RESPONSE, ["time" => $string]);
		}else{
			$sender->sendMessage(Phrases::VAR_em . $string . Phrases::VAR_info . " left before server restart");
		}
	}
}
