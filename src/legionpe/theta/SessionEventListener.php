<?php

/**
 * LegionPE-Theta
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

namespace legionpe\theta;

use legionpe\theta\queue\JoinTriggerRunnable;
use legionpe\theta\queue\Queue;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

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
			$this->main->queueFor($player->getId(), true, Queue::QUEUE_SESSION)
				->pushToQueue(new JoinTriggerRunnable($this->main, $player));
		}else{
			$ses->onJoin();
		}
	}
	/**
	 * @param PlayerCommandPreprocessEvent $event
	 * @priority LOWEST
	 */
	public function onCommandPreprocess(PlayerCommandPreprocessEvent $event){
		$ses = $this->main->getSession($player = $event->getPlayer());
		if(!($ses instanceof Session)){
			$player->sendMessage("Please wait. We are still preparing your account. You cannot type anything until your account is ready.");
			$event->setMessage("");
			return;
		}
		$ses->onCmd($event);
	}
	
	public function onPlayerMove(PlayerMoveEvent $event) {
		if(!this->plugin->isPlayerAuthenticated($event->getPlayer())){
			if($event->getPlayer()->hasPermission("need permission here!")) {
				$event->setCancelled(true);
				$event->getPlayer()->onGround(true);
			}
		}
	}
	
	// TODO: lock player if not authenticated

	public function onQuit(PlayerQuitEvent $event){
		$this->main->endSession($event->getPlayer());
		$event->setQuitMessage("");
	}
}
