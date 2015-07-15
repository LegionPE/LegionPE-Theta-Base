<?php

/*
 * LegionPE Theta
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

namespace legionpe\theta\command;

use legionpe\theta\BasePlugin;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

class PhpCommand extends ThetaCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "php", "Execute PHp code directly", "/php <PHP code ...>");
	}
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!($sender instanceof ConsoleCommandSender)){
			return true;
		}
		$code = implode(" ", $args);
		$this->getPlugin()->getLogger()->alert("Executing PHP code: $code");
		$this->getPlugin()->evaluate($code);
		return true;
	}
	public function testPermissionSilent(CommandSender $sender){
		return $sender instanceof ConsoleCommandSender;
	}
}
