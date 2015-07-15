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
