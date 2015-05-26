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
use legionpe\theta\query\IncrementWarningPointsQuery;
use legionpe\theta\query\LogWarningQuery;
use legionpe\theta\query\NextIdQuery;
use legionpe\theta\Session;
use legionpe\theta\utils\MUtils;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class ExecuteWarningRunnable implements Runnable{
	/** @var BasePlugin */
	private $main;
	/** @var NextIdQuery */
	private $wid;
	/** @var int */
	private $uid;
	/** @var number */
	private $clientId;
	/** @var int */
	private $id;
	/** @var int */
	private $points;
	/** @var CommandSender */
	private $issuer;
	/** @var string */
	private $msg;
	/** @var int */
	private $creation, $expiry;
	public function __construct(BasePlugin $main, NextIdQuery $wid, $uid, $clientId, $id, $points, CommandSender $issuer, $msg){
		$this->main = $main;
		$this->wid = $wid;
		$this->uid = $uid;
		$this->clientId = $clientId;
		$this->id = $id;
		$this->points = $points;
		$this->issuer = $issuer;
		$this->msg = $msg;
//		$this->expiry = ($this->creation = time()) + $duration;
		$this->creation = time();
		$this->expiry = PHP_INT_MAX;
	}
	public function canRun(){
		return $this->wid->hasResult();
	}
	public function run(){
		$id = $this->wid->getId();
		if($id === -1){
			$this->issuer->sendMessage("Failed to create warning");
			$this->issuer = null; // release instance
			return;
		}
		new LogWarningQuery($this->main, $this->wid, $this->uid, $this->clientId, $this->issuer, $this->msg, $this->creation, $this->expiry);
		foreach($this->main->getSessions() as $ses){
			if($ses->getUid() === $this->uid){
				$ses->addWarningPoints($this->points);
				$done = true;
				$this->issuer->sendMessage(TextFormat::GREEN . "Warning points have been successfully issued to player.");
				$this->execWarnOn($ses);
				break;
			}
		}
		if(!isset($done)){ // you think leaving the game can keep you away from trouble?
			new IncrementWarningPointsQuery($this->main, $this->points, $this->uid);
			$this->issuer->sendMessage(TextFormat::GREEN . "Warning points have been successfully issued to offline player.");
		}
		$this->issuer = null; // release instance
	}
	private function execWarnOn(Session $ses){
		$ses->getPlayer()->sendMessage(TextFormat::DARK_RED . "ATTENTION\n" . TextFormat::RED . str_repeat("~", 50));
		$ses->getPlayer()->sendMessage(TextFormat::RED . "You have received a warning from {$this->issuer->getName()}.");
		$ses->getPlayer()->sendMessage(TextFormat::RED . "The warning message is as follows:");
		$ses->getPlayer()->sendMessage(TextFormat::YELLOW . $this->msg);
		$ses->getPlayer()->sendMessage(TextFormat::RED . "You have received $this->points new warning points with this warning.");
		$ses->getPlayer()->sendMessage(TextFormat::DARK_AQUA . "You now have {$ses->getWarningPoints()} warning points in your account.");
		$conseq = Settings::getWarnPtsConseq($ses->getWarningPoints());
		if($conseq->banLength){
			$ses->getPlayer()->sendMessage(TextFormat::YELLOW . "You are going to be banned for " . MUtils::time_secsToString($conseq->banLength) . ".");
		}elseif($conseq->muteSecs){
			$ses->getPlayer()->sendMessage(TextFormat::YELLOW . "You are going to be muted for " . MUtils::time_secsToString($conseq->muteSecs) . ".");
			// TODO mute
		}
	}
}
