<?php

/**
 * LegionPE
 * Copyright (C) 2015 PEMapModder
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace legionpe\theta\command;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\session\CoinsCommand;
use legionpe\theta\command\session\TransferCommand;
use legionpe\theta\config\Settings;
use legionpe\theta\Session;
use pocketmine\command\Command;
use pocketmine\command\CommandMap;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\utils\TextFormat;

abstract class ThetaCommand extends Command implements PluginIdentifiableCommand{
	/** @var BasePlugin */
	private $plugin;
	public function __construct(BasePlugin $plugin, $name, $desc, $usage, $aliases = []){
		parent::__construct($name, $desc, $usage, (array) $aliases);
		$this->plugin = $plugin;
	}
	/**
	 * @return BasePlugin
	 */
	public function getPlugin(){
		return $this->plugin;
	}
	public function getSession($player){
		return $this->getPlugin()->getSession($player);
	}
	public static function registerAll(BasePlugin $main, CommandMap $map){
		$map->registerAll("l", [
			new CoinsCommand($main),
			new TransferCommand($main, ["pvp", "kitpvp"], "Kit PvP", Settings::CLASS_KITPVP),
			new TransferCommand($main, ["parkour", "pk"], "Parkour", Settings::CLASS_PARKOUR),
			new TransferCommand($main, ["spleef", "spf"], "Spleef", Settings::CLASS_SPLEEF),
			new TransferCommand($main, ["infected", "inf"], "Infected", Settings::CLASS_INFECTED),
			new TransferCommand($main, ["classic", "cls"], "Classic PvP", Settings::CLASS_CLASSICAL),
			new TransferCommand($main, ["hub", "spawn", "quit", "home", "back", "lobby"], "Hub", Settings::CLASS_HUB),
			new PhpCommand($main)
		]);
	}
	protected function sendUsage($sender){
		if($sender instanceof Session){
			$sender = $sender->getPlayer();
		}
		if($sender instanceof CommandSender){
			$sender->sendMessage(TextFormat::RED . "Usage: " . $this->getUsage());
		}
	}
	protected function notOnline($sender){
		if($sender instanceof Session){
			$sender = $sender->getPlayer();
		}
		if($sender instanceof CommandSender){
			$sender->sendMessage(TextFormat::RED . "There is no player online by that name.");
		}
	}
}
