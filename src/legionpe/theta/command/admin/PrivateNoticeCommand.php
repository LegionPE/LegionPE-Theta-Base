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
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;
use pocketmine\command\CommandSender;

class PrivateNoticeCommand extends ModeratorCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "pn", "Send a private notice to a player", "/pn <player> <message ...>");
	}
	public function hasPermission(Session $session){
		return $session->isAdmin();
	}
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return;
		}
		if(!isset($args[1])){
			$this->sendUsage($sender);
			return;
		}
		$name = array_shift($args);
		$ses = $this->getSession($name);
		if($ses === null){
			$this->notOnline($sender, $name);
			return;
		}
		$ses->send(Phrases::CMD_PRIV_NOTICE_RECIPIENT, ["msg" => $msg = implode(" ", $args)]);
		$sender->sendMessage("PN to $ses: $msg");
	}
}
