<?php

namespace legionpe\theta;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;

class Session{
	/** @var Player */
	private $player;
	public function __construct(Player $player, $loginData){
		$this->player = $player;
	}
	/**
	 * @return Player
	 */
	public function getPlayer(){
		return $this->player;
	}
	public function onJoin(PlayerJoinEvent $event){

	}
}
