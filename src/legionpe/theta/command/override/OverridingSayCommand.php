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
use legionpe\theta\chat\ChatType;
use legionpe\theta\command\admin\ModeratorCommand;
use legionpe\theta\config\Settings;
use pocketmine\command\CommandSender;

class OverridingSayCommand extends ModeratorCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "say", "Network broadcast", "/say [.]<message ...>");
	}
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}
		$msg = implode(" ", $args);
		$local = true;
		if(substr($msg, 0, 1) === "."){
			$msg = substr($msg, 1);
			$local = false;
		}
		$type = ChatType::get($this->getMain(), ChatType::SERVER_BROADCAST, $sender->getName(), $msg, $local ? Settings::$LOCALIZE_CLASS : Settings::CLASS_ALL, []);
		$type->push();
		return true;
	}
}
