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

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;
use legionpe\theta\config\Settings;
use legionpe\theta\lang\Phrases;
use pocketmine\Player;
use pocketmine\Server;

class TransferServerQuery extends AsyncQuery{
	/** @var int */
	public $class;
	/** @var bool */
	private $checkPlayers;
	/** @var string */
	private $exactName;
	public function __construct(BasePlugin $plugin, $class, $checkPlayers, $exactName){
		$this->class = $class;
		$this->checkPlayers = $checkPlayers;
		$this->exactName = $exactName;
		parent::__construct($plugin);
	}
	public function getQuery(){
		$checkPlayers = $this->checkPlayers ? " AND online_players<max_players" : "";
		return "SELECT ip,port FROM server_status WHERE unix_timestamp()-last_online < 5 AND class=$this->class$checkPlayers ORDER BY online_players ASC LIMIT 1";
	}
	public function getResultType(){
		return self::TYPE_ASSOC;
	}
	public function getExpectedColumns(){
		return [
			"ip" => self::COL_STRING,
			"port" => self::COL_INT
		];
	}
	public function onCompletion(Server $server){
		$player = $server->getPlayerExact($this->exactName);
		if($player instanceof Player){
			$result = $this->getResult();
			if($result["resulttype"] === self::TYPE_ASSOC){
				$row = $result["result"];
				/** @var string $ip */
				/** @var int $port */
				extract($row);
				$main = BasePlugin::getInstance($server);
				$main->transfer($player, $ip, $port, Phrases::VAR_success . "Transferring you to a " . Settings::$CLASSES_NAMES[$this->class] . " server.");
			}else{
				$player->sendMessage(Phrases::VAR_error . "No " . Settings::$CLASSES_NAMES[$this->class] . " servers available!");
			}
		}
	}
}
