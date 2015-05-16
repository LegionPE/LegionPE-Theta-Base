<?php

namespace legionpe\theta\queue;

use legionpe\theta\BasePlugin;
use legionpe\theta\query\AsyncQuery;
use legionpe\theta\query\LoginQuery;
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

		}
	}
}
