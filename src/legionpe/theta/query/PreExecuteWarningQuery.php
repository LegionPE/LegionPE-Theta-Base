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
use legionpe\theta\MuteIssue;
use legionpe\theta\Session;
use legionpe\theta\utils\MUtils;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class PreExecuteWarningQuery extends NextIdQuery{
	/** @var int */
	private $uid;
	/** @var number */
	private $clientId;
	/** @var int */
	private $id;
	/** @var int */
	private $points;
	/** @var int|CommandSender */
	private $issuer;
	/** @var string */
	private $msg;
	/** @var int */
	private $creation, $expiry;
	public function __construct(BasePlugin $main, $uid, $clientId, $id, $points, CommandSender $issuer, $msg){
		$this->uid = $uid;
		$this->clientId = $clientId;
		$this->id = $id;
		$this->points = $points;
		$this->issuer = $main->storeObject($issuer);
		$this->msg = $msg;
//		$this->expiry = ($this->creation = time()) + $duration;
		$this->creation = time();
		$this->expiry = PHP_INT_MAX;
		parent::__construct($main, self::WARNING);
	}
	public function onCompletion(Server $server){
		$id = $this->getId();
		$main = BasePlugin::getInstance($server);
		/** @var CommandSender $issuer */
		$issuer = $main->fetchObject($id);
		if($id === -1){
			$issuer->sendMessage("Failed to create warning");
			$issuer = null; // release instance
			return;
		}
		new LogWarningQuery($main, $id, $this->uid, $this->clientId, $issuer, $this->points, $this->msg, $this->creation, $this->expiry);
		foreach($main->getSessions() as $ses){
			if($ses->getUid() === $this->uid){
				$ses->addWarningPoints($this->points);
				$done = true;
				$issuer->sendMessage("Warning points have been successfully issued to player.");
				$this->execWarnOn($issuer, $ses);
				break;
			}
		}
		if(!isset($done)){ // you think leaving the game can keep you away from trouble?
			new IncrementWarningPointsQuery($main, $this->points, $this->uid);
			$issuer->sendMessage(TextFormat::GREEN . "Warning points have been successfully issued to offline player.");
		}
	}
	private function execWarnOn(CommandSender $issuer, Session $ses){
		$msg = $ses->translate(Phrases::WARNING_RECEIVED_NOTIFICATION, [
			"issuer" => $issuer->getName(),
			"message" => $this->msg,
			"points" => $this->points,
			"totalpoints" => $ses->getWarningPoints()
		]);
		$conseq = Settings::getWarnPtsConseq($ses->getWarningPoints(), $this->creation);
		if($conseq->banLength){
			$msg .= $ses->translate(Phrases::WARNING_BANNED_NOTIFICATION, ["length" => MUtils::time_secsToString($conseq->banLength)]);
			$msg = "\n" . $msg;
			$ses->getPlayer()->kick($msg, false);
		}elseif($conseq->muteSecs){
			$mute = new MuteIssue;
			$mute->cid = $this->clientId;
			$mute->ip = $ses->getPlayer()->getAddress();
			$mute->uid = $this->uid;
			$mute->length = $conseq->muteSecs;
			$mute->msg = $this->msg;
			$mute->since = $this->creation;
			$mute->src = $issuer->getName();
			$ses->getPlayer()->sendMessage($msg);
		}
	}
}
