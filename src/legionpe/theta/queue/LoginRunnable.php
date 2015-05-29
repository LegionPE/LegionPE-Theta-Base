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

namespace legionpe\theta\queue;

use legionpe\theta\BasePlugin;
use legionpe\theta\config\Settings;
use legionpe\theta\query\AsyncQuery;
use legionpe\theta\query\LoginQuery;
use legionpe\theta\utils\MUtils;
use pocketmine\utils\TextFormat;

class LoginRunnable implements Runnable{
	/** @var BasePlugin */
	private $main;
	/** @var LoginQuery */
	private $login;
	/** @var int */
	private $sesId;
	public function __construct(BasePlugin $main, LoginQuery $login, $sesId){
		$this->main = $main;
		$this->login = $login;
		$this->sesId = $sesId;
	}
	public function canRun(){
		return $this->login->hasResult();
	}
	public function run(){
		foreach($this->main->getServer()->getOnlinePlayers() as $player){
			if($player->getId() === $this->sesId){
				break;
			}
		}
		if(!isset($player)){
			return;
		}
		/** @var bool $success */
		/** @var string $query */
		extract($this->login->getResult());
		if(!$success){
			$player->close(TextFormat::RED . "Sorry, our server has encountered an internal error when trying to retrieve your data from the database.");
			return;
		}
		/** @var int $resulttype */
		if($resulttype === AsyncQuery::TYPE_RAW){
			$loginData = null;
		}else{
			/** @var mixed[] $result */
			$loginData = $result;
			$conseq = Settings::getWarnPtsConseq($this->login->totalWarnPts, $loginData["lastwarn"]);
			if($conseq->banLength > 0){
				$player->kick(TextFormat::RED . "You are banned.\nYou have accumulated " . TextFormat::DARK_PURPLE . $this->login->totalWarnPts . TextFormat::RED . " warning points,\nand you still have " . TextFormat::BLUE . MUtils::time_secsToString($conseq->banLength) . TextFormat::RED . " before you are unbanned.\n" . TextFormat::AQUA . "Believe this to be a mistake? Contact us with email at " . TextFormat::DARK_PURPLE . "support@legionpvp.eu");
			}
		}
		$this->main->newSession($player, $loginData);
	}
}
