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

namespace legionpe\theta\command\override;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\admin\ModeratorCommand;
use pocketmine\command\CommandSender;

class MBCommand extends ModeratorCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "mb", "Deprecated command, use /warn instead", "Deprecated command, use /warn instead", ["ban"]);
	}
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}
		return false;
	}
}
