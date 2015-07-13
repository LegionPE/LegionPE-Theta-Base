<?php

/**
 * Theta
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

namespace legionpe\theta\command\override;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\ThetaCommand;
use legionpe\theta\config\Settings;
use pocketmine\command\CommandSender;
use pocketmine\command\defaults\StatusCommand;
use pocketmine\command\RemoteConsoleCommandSender;
use pocketmine\utils\TextFormat;

class OverridingStatusCommand extends ThetaCommand{
	/** @var StatusCommand */
	private $status;
	public function __construct(BasePlugin $main){
		parent::__construct($main, "status", "Show server status", "/status");
		$this->status = new StatusCommand("status");
	}
	public function execute(CommandSender $sender, $commandLabel, array $args){
		$rcon = new RemoteConsoleCommandSender;
		$this->status->execute($rcon, "status", []);
		$messages = explode("\n", $rcon->getMessage());
		$output = TextFormat::GREEN . "=== GLOBAL STATUS ===\n";
		$this->getMain()->getPlayersCount($total, $max, $ctotal, $cmax);
		$this->getMain()->getServersCount($servers, $cservers);
		$red = TextFormat::RED;
		$output .= TextFormat::GOLD . "LegionPE Network:$red $total slots of $max used in $servers server(s)\n";
		$output .= TextFormat::GOLD . Settings::$CLASSES_NAMES[Settings::$LOCALIZE_CLASS] . ":$red $total slots of $max used in $servers server(s)\n";
		$output .= TextFormat::GREEN . "=== LOCAL STATUS (" . TextFormat::AQUA . Settings::$LOCALIZE_IP . ":" . Settings::$LOCALIZE_PORT . TextFormat::GREEN . ") ===\n";
		$sender->sendMessage($output);
		array_shift($messages);
		foreach($messages as $message){
			$sender->sendMessage($message);
		}
	}
}
