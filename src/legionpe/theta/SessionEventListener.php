<?php

namespace legionpe\theta;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

/* extensible */
class SessionEventListener implements Listener{
	/** @var BasePlugin */
	private $main;
	public function __construct(BasePlugin $main){
		$this->main = $main;
	}
	public function onJoin(PlayerJoinEvent $event){
		$event->setJoinMessage("");
		$player = $event->getPlayer();
		$ses = $this->main->getSession($player);
		if($ses === null){
			// TODO queue
		}
		// TODO call
	}
}
