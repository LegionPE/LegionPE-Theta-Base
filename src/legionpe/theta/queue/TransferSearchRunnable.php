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

namespace legionpe\theta\queue;

use legionpe\theta\BasePlugin;
use legionpe\theta\config\Settings;
use legionpe\theta\lang\Phrases;
use legionpe\theta\query\TransferServerQuery;
use legionpe\theta\Session;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/** @deprecated */
class TransferSearchRunnable implements Runnable{
	/** @var BasePlugin */
	private $plugin;
	/** @var Player */
	private $player;
	/** @var TransferServerQuery */
	private $query;
	public function __construct(BasePlugin $plugin, Player $player, TransferServerQuery $query){
		$this->plugin = $plugin;
		$this->player = $player;
		$this->query = $query;
	}
	public function canRun(){
		return $this->query->hasResult(); // TODO DEPRECATION move to onCompletion of SearchServerQuery
	}
	public function run(){
		if(!$this->player->isConnected()){
			return;
		}
		$result = $this->query->getResult();
		$name = Settings::$CLASSES_NAMES[$this->query->class];
		if(!is_array($result)){
			if(($ses = $this->plugin->getSession($this->player)) instanceof Session){
				$ses->send(Phrases::CMD_TRANSFER_ERR_NO_SERVERS, ["class" => $name]);
			}else{
				$this->player->sendMessage(TextFormat::RED . "Error: no servers for $name are online.");
			}
			return;
		}
		/** @var string $ip */
		/** @var int $port */
		extract($result);
		if(($ses = $this->plugin->getSession($this->player)) instanceof Session){
			$ses->send(Phrases::CMD_TRANSFER_SUCCESS, ["class" => $name, "ip" => $ip, "port" => $port]);
		}else{
			$this->player->sendMessage(TextFormat::GREEN . "Transferring you to $ip:$port ($name server)...");
		}
		$this->plugin->transfer($this->player, $ip, $port, "", true);
	}
	public function __debugInfo(){
		return [
			"query" => $this->query
		];
	}
}
