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

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;
use legionpe\theta\config\Settings;
use legionpe\theta\Session;

class SaveSinglePlayerQuery extends AsyncQuery{
	/** @var string $data serialization of getColumns */
	private $data;
	private $coinsDelta;
	public function __construct(BasePlugin $plugin, Session $session, $status){
		parent::__construct($plugin);
		$data = $this->getColumns($session, $status);
		$this->data = serialize($data);
	}
	public function getQuery(){
		while(!isset($this->data));
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
	public function getResultType(){
		return self::TYPE_RAW;
	}
	/**
	 * @param Session $s
	 * @param int $status
	 * @return mixed[][]|mixed[]
	 */
	protected function getColumns(Session $s, $status){
		$coins = $s->getCoins();
		$this->coinsDelta = $s->getAndUpdateCoinsDelta();
		return [
			"uid" => ["v" => $s->getUid(), "noupdate" => true],
			"name" => ["v" => strtolower($s->getPlayer()->getName())],
			"nicks" => "|" . implode("|", $s->getNicks()) . "|",
			"lastip" => $s->getPlayer()->getAddress(),
			"status" => $status,
			"lastses" => Settings::$LOCALIZE_CLASS,
			"authuuid" => $s->getPlayer()->getUniqueId(),
			"coins" => ["v" => $coins, "noupdate" => true],
			"hash" => ["v" => $s->getPasswordHash(), "noupdate" => true],
			"pwprefix" => ["v" => $s->getPasswordPrefix(), "noupdate" => true],
			"pwlen" => ["v" => $s->getPasswordLength(), "noupdate" => true],
			"registration" => ["v" => $s->getRegisterTime(), "noupdate" => true],
			"laston" => time(),
			"ontime" => (int) $s->getAndUpdateOntime(),
			"config" => $s->getAllSettings(),
			"skin1" => ["v" => $s->getCurrentFaceSkin(), "bin" => true],
			"lastgrind" => $s->getLastGrind(),
			"rank" => ["v" => $s->getRank(), "noupdate" => true],
			"warnpts" => $s->getWarningPoints(),
			"lastwarn" => $s->getLastWarnTime(),
			"tid" => $s->getTeamId(),
			"teamrank" => $s->getTeamRank(),
			"teamjoin" => $s->getTeamJoinTime(),
			"ignorelist" => "," . implode(",", $s->getIgnoreList()) . ","
		];
	}
	protected function queryFinalProcess($query){
		return $query . ",coins=coins+$this->coinsDelta";
	}
}
