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

namespace legionpe\theta\command\session;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\SessionCommand;
use legionpe\theta\Session;
use pocketmine\utils\TextFormat;

class CoinsCommand extends SessionCommand{
	public function __construct(BasePlugin $plugin){
		parent::__construct($plugin, "coins", "View coins", "/coins");
	}
	protected function run(array $args, Session $sender){
		return TextFormat::DARK_GREEN . "You have {$sender->getCoins()} coins.";
	}
}
