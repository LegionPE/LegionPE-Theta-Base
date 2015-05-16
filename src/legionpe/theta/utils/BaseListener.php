<?php

namespace legionpe\theta\utils;

use legionpe\theta\BasePlugin;
use legionpe\theta\query\LoginQuery;
use legionpe\theta\queue\LoginRunnable;
use legionpe\theta\queue\Queue;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;

class BaseListener implements Listener{
	/** @var BasePlugin */
	private $main;
	public function __construct(BasePlugin $main){
		$this->main = $main;
	}
	public function onPreLogin(PlayerPreLoginEvent $event){
		$player = $event->getPlayer();
		$login = new LoginQuery($this->main, $player->getName());
		$this->main->queueFor($player->getId(), true, Queue::QUEUE_SESSION)->pushToQueue(new LoginRunnable($this->main, $login, $player->getId()));
	}
}
