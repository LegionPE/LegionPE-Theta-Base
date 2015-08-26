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
use legionpe\theta\Session;
use pocketmine\Server;

class SaveSinglePlayerQuery extends AsyncQuery{
	const INBOX_UNREAD = 0;
	const INBOX_READ = 1;
	private $uid;
	/** @var string $data serialization of getColumns */
	private $data;
	private $coinsDelta;
	public function __construct(BasePlugin $plugin, Session $session, $status){
		$data = $this->getColumns($session, $status);
		$this->uid = $session->getUid();
		$this->data = $data;
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
			"hash" => ["v" => $session->getPasswordHash(), "noupdate" => !$session->doHashSaves],
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
	public function getUpdateQuery(){
		$query = "INSERT INTO" . " users(";
		/** @var mixed[][] $data */
		$data = $this->data;
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
	public function onPreQuery(\mysqli $db){
		$db->query($q = $this->getUpdateQuery());
		if($db->error){
			echo $db->error . "\n";
		}
	}
	public function getQuery(){
		return "SELECT msgid,msg,args FROM inbox WHERE uid=$this->uid AND status=" . self::INBOX_UNREAD;
	}
	public function getResultType(){
		return self::TYPE_ALL;
	}
	public function getExpectedColumns(){
		return [
			"msgid" => self::COL_INT,
			"msg" => self::COL_STRING,
			"args" => self::COL_STRING,
		];
	}
	public function reportDebug(){
		return false;
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		$ses = $main->getSession($this->uid);
		if($ses instanceof Session){
			$result = $this->getResult();
			if(isset($result["result"]) and count($result["result"]) > 0){
				$ses->send(Phrases::CHAT_INBOX_START, count($result["result"]));
				$read = [];
				foreach($result["result"] as $row){
					$read[] = $row["msgid"];
					$ses->sendMessage($row["msg"], json_decode($row["args"]));
				}
				new MarkPrivateMessageReadQuery($main, $read);
				$ses->send(Phrases::CHAT_INBOX_END, count($result["result"]));
			}
		}
	}
}
