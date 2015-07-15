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
use legionpe\theta\Session;
use pocketmine\command\CommandSender;

class WarnCommand extends ModeratorCommand{
	const MODS = 0;
	const SWEAR = 1;
	const CAPS = 2;
	const ADS = 3;
	const DISOBEY = 4;
	const IMPOSE = 5;
	const SPAM = 6;
	const MISC = 7;
	/** @var int[] */
	public static $IDS_TO_POINTS = [
		self::MODS => 10,
		self::SWEAR => 5,
		self::CAPS => 4,
		self::ADS => 7,
		self::DISOBEY => 6,
		self::IMPOSE => 6,
		self::SPAM => 7,
		self::MISC => 5
	];
	/** @var string[] */
	public static $IDS_TO_MESSAGES = [
		self::MODS => "The use of client mods is disallowed.",
		self::SWEAR => "Please avoid chatting with inappropriate language or about adult content.",
		self::CAPS => "Please do not use abusive caps.",
		self::ADS => "Please do not advertize.",
		self::DISOBEY => "Please follow instructions from staff members.",
		self::IMPOSE => "Please do not impose as staff members.",
		self::SPAM => "Please do not SPAM.",
		self::MISC => ""
	];
	/** @var string */
	private $realname;
	/** @var int */
	private $id;
	/** @var int */
	private $points;
	/**
	 * @param BasePlugin $plugin
	 * @param string[] $aliases
	 * @param string $realname
	 * @param int $id
	 */
	public function __construct(BasePlugin $plugin, array $aliases, $realname, $id){
		parent::__construct($plugin, $cmd = "w" . array_shift($aliases), "Warn about $realname", "/$cmd <player> [points = " . ($this->points = self::$IDS_TO_POINTS[$id]) . "] [message ]", $aliases);
		$this->realname = $realname;
		$this->id = $id;
	}
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}
		if(!isset($args[0])){
			$this->sendUsage($sender);
			return true;
		}
		$player = array_shift($args);
		$ses = $this->getSession($player);
		if(!($ses instanceof Session)){
			$this->notOnline($sender);
			return true;
		}
		$points = $this->points;
		$msg = self::$IDS_TO_MESSAGES[$this->id];
		if(isset($args[0])){
			$points = (int)array_shift($args);
			if(isset($args[0])){
				$msg = implode(" ", $args);
			}
		}
		$ses->warn($this->id, $points, $sender, $msg);
		return true;
	}
}
