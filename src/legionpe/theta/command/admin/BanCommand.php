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

namespace legionpe\theta\command\admin;

use legionpe\theta\BasePlugin;
use legionpe\theta\query\WarningQuery;
use legionpe\theta\Session;
use legionpe\theta\utils\MUtils;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class BanCommand extends ModeratorCommand{
	/**
	 * @param BasePlugin $plugin
	 */
	public function __construct(BasePlugin $plugin){ //
		parent::__construct(
			$plugin,
			"ban",
			"Ban a player",
			"/ban <player> <duration> <format (example: hours, days, weeks, months, years> <message>",
			["tban"]
		);
	}
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}
		if(!isset($args[0]) or !isset($args[1]) or !isset($args[2]) or !isset($args[3])){
			$this->sendUsage($sender);
			return true;
		}
		$player = $this->getPlugin()->getServer()->getPlayer($args[0]);
		if(!($player instanceof Player)){
			return $args[0] . " is not online.";
		}
		if(!is_numeric($args[1])){
			return "Your duration value has to be a number.";
		}
		$duration = null;
		$args[1] = (int) $args[1];
		switch($args[2]){
			case "h":
			case "hour":
			case "hours":
				$duration = $args[1] * 60 * 60;
				break;
			case "d":
			case "day":
			case "days":
				$duration = $args[1] * 60 * 60 * 24;
				break;
			case "w":
			case "week":
			case "weeks":
				$duration = $args[1] * 60 * 60 * 24 * 7;
				break;
			case "m":
			case "month":
			case "months":
				$duration = $args[1] * 60 * 60 * 24 * 28;
				break;
			case "y":
			case "year":
			case "years":
				$duration = $args[1] * 60 * 60 * 24 * 365;
				break;
		}
		if($duration === null){
			return "Duration not specified.";
		}
		unset($args[0]);
		unset($args[1]);
		unset($args[2]);
		$message = implode(" ", $args);
		new WarningQuery($this->getPlugin(), $this->getSession($sender), $this->getSession($player), $message, 0, $duration);
		$player->kick(TextFormat::RED . "You have been banned. Duration: " . MUtils::time_secsToString($duration) ." \n" . TextFormat::RED . "Message: " . TextFormat::AQUA . $message);
		return "Player has been banned.";
	}
}
