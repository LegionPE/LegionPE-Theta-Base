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
use legionpe\theta\Session;

class SaveSinglePlayerQuery extends AsyncQuery{
	/** @var string $data serialization of getColumns */
	private $data;
	private $coinsDelta;
	public function __construct(BasePlugin $plugin, Session $session, $status){
		$data = $this->getColumns($session, $status);
		$this->data = serialize($data);
		parent::__construct($plugin);
	}
	/**
	 * @param Session $session
	 * @param int $status
	 * @return mixed[][]|mixed[]
	 */
	protected function getColumns(Session $session, $status){
		$coins = $session->getCoins();
		$this->coinsDelta = $session->getAndUpdateCoinsDelta();
		$skin = $session->getCurrentFaceSkin();
		return [
			"uid" => ["v" => $session->getUid(), "noupdate" => true],
			"name" => ["v" => strtolower($session->getPlayer()->getName())],
			"nicks" => "|" . implode("|", $session->getNicks()) . "|",
			"lastip" => $session->getPlayer()->getAddress(),
			"status" => $status,
			"lastses" => Settings::$LOCALIZE_CLASS,
			"authuuid" => $session->getPlayer()->getUniqueId(),
			"coins" => ["v" => $coins, "noupdate" => true],
			"hash" => ["v" => $session->getPasswordHash(), "noupdate" => true],
			"pwprefix" => ["v" => $session->getPasswordPrefix(), "noupdate" => true],
			"pwlen" => ["v" => $session->getPasswordLength(), "noupdate" => true],
			"registration" => ["v" => $session->getRegisterTime(), "noupdate" => true],
			"laston" => time(),
			"ontime" => (int)$session->getAndUpdateOntime(),
			"config" => $session->getAllSettings(),
			"skin1" => ["v" => substr($skin, 0, 128), "bin" => true],
			"skin2" => ["v" => substr($skin, 128, 128), "bin" => true],
			"lastgrind" => $session->getLastGrind(),
			"rank" => ["v" => $session->getRank(), "noupdate" => true],
			"warnpts" => $session->getWarningPoints(),
			"lastwarn" => $session->getLastWarnTime(),
			"tid" => $session->getTeamId(),
			"teamrank" => $session->getTeamRank(),
			"teamjoin" => $session->getTeamJoinTime(),
			"ignorelist" => "," . implode(",", $session->getIgnoreList()) . ","
		];
	}
	public function getQuery(){
		$query = "INSERT INTO" . " users(";
		/** @var mixed[][] $data */
		$data = unserialize($this->data);
		/** @var string[] $cols */
		$cols = [];
		/** @var string[] $inserts */
		$inserts = [];
		foreach($data as $column => $datum){
			$cols[] = $column;
			if(!is_array($datum)){
				$inserts[] = $this->esc($datum);
			}elseif(!isset($datum["noinsert"])){
				$inserts[] = $this->esc($datum["v"], isset($datum["bin"]));
			}
		}
		$query .= implode(",", $cols);
		$query .= ")VALUES(";
		$query .= implode(",", $inserts);
		$query .= ")ON DUPLICATE KEY UPDATE ";
		foreach($data as $column => $datum){
			if(!is_array($datum)){
				$query .= $column . "=" . $this->esc($datum) . ",";
			}elseif(!isset($datum["noupdate"])){
				$query .= $column . "=" . $this->esc($datum["v"], isset($datum["bin"])) . ",";
			}
		}
		return $this->queryFinalProcess(substr($query, 0, -1));
	}
	protected function queryFinalProcess($query){
		return $query . ",coins=coins+$this->coinsDelta";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
}
