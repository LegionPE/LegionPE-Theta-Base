<?php

namespace legionpe\theta\queue;

use legionpe\theta\BasePlugin;
use legionpe\theta\query\NextIdQuery;

class NewSessionRunnable implements Runnable{
	/** @var BasePlugin */
	private $main;
	/** @var NextIdQuery */
	private $query;
	/** @var int */
	private $sesId;
	public function __construct(BasePlugin $plugin, NextIdQuery $query, $sesId){
		$this->main = $plugin;
		$this->query = $query;
		$this->sesId = $sesId;
	}
	public function canRun(){
		return $this->query->hasResult();
	}
	public function run(){
		$uid = $this->query->getResult()["id"];
		foreach($this->main->getServer()->getOnlinePlayers() as $player){
			if($player->getId() === $this->sesId){
				break;
			}
		}
		if(!isset($player)){
			return;
		}
		$this->main->newSession($player, BasePlugin::getDefaultLoginData($uid, $player));
	}
}
