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

namespace legionpe\theta;

use legionpe\theta\lang\Phrases;
use legionpe\theta\queue\JoinTriggerRunnable;
use legionpe\theta\queue\Queue;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\Player;

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
			$player->sendMessage(Phrases::VAR_wait . "Please wait a moment while we are preparing your account.");
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
			$player->sendMessage(Phrases::VAR_wait . "Please wait. We are still preparing your account. You cannot type anything until your account is ready.");
			$event->setMessage("");
			return;
		}
		if($ses->onCmd($event) === false){
			$event->setCancelled();
		}
	}
	public function onDamage(EntityDamageEvent $event){
		$entity = $event->getEntity();
		if($entity instanceof Player){
			$session = $this->main->getSession($entity);
			if(!($session instanceof Session)){
				$event->setCancelled();
				return;
			}
			if($session->onDamage($event) === false){
				$event->setCancelled();
			}
		}
	}
	public function onDeath(PlayerDeathEvent $event){
		$session = $this->main->getSession($event->getEntity());
		if(!($session instanceof Session)){
			return;
		}
		$session->onDeath($event);
	}
	public function onMove(PlayerMoveEvent $event){
		$session = $this->main->getSession($event->getPlayer());
		if(!($session instanceof Session)){
			$event->setCancelled();
			return;
		}
		if($session->onMove($event) === false){
			$event->setCancelled();
		}
	}
	public function onConsume(PlayerItemConsumeEvent $event){
		$session = $this->main->getSession($event->getPlayer());
		if(!($session instanceof Session)){
			$event->setCancelled();
			return;
		}
		if($session->onConsume($event) === false){
			$event->setCancelled();
		}
	}
	public function onDropItem(PlayerDropItemEvent $event){
		$session = $this->main->getSession($event->getPlayer());
		if(!($session instanceof Session)){
			$event->setCancelled();
			return;
		}
		if($session->onDropItem($event) === false){
			$event->setCancelled();
		}
	}
	public function onInteract(PlayerInteractEvent $event){
		$session = $this->main->getSession($event->getPlayer());
		if(!($session instanceof Session)){
			$event->setCancelled();
			return;
		}
		if($session->onInteract($event) === false){
			$event->setCancelled();
		}
	}
	public function onRespawn(PlayerRespawnEvent $event){
		$session = $this->main->getSession($event->getPlayer());
		if(!($session instanceof Session)){
			return;
		}
		$session->onRespawn($event);
	}
	public function onBreak(BlockBreakEvent $event){
		$session = $this->main->getSession($event->getPlayer());
		if(!($session instanceof Session)){
			$event->setCancelled();
			return;
		}
		if($session->onBreak($event) === false){
			$event->setCancelled();
		}
	}
	public function onPlace(BlockPlaceEvent $event){
		$session = $this->main->getSession($event->getPlayer());
		if(!($session instanceof Session)){
			$event->setCancelled();
			return;
		}
		if($session->onPlace($event) === false){
			$event->setCancelled();
		}
	}
	public function onOpenInv(InventoryOpenEvent $event){
		$session = $this->main->getSession($event->getPlayer());
		if(!($session instanceof Session)){
			$event->setCancelled();
			return;
		}
		if($session->onOpenInv($event) === false){
			$event->setCancelled();
		}
	}
	public function onPickupItem(InventoryPickupItemEvent $event){
		$holder = $event->getInventory()->getHolder();
		if(!($holder instanceof Player)){
			return;
		}
		$session = $this->main->getSession($holder);
		if(!($session instanceof Session)){
			$event->setCancelled();
			return;
		}
		if($session->onPickupItem($event) === false){
			$event->setCancelled();
		}
	}
	public function onPickupArrow(InventoryPickupArrowEvent $event){
		$holder = $event->getInventory()->getHolder();
		if(!($holder instanceof Player)){
			return;
		}
		$session = $this->main->getSession($holder);
		if(!($session instanceof Session)){
			$event->setCancelled();
			return;
		}
		if($session->onPickupArrow($event) === false){
			$event->setCancelled();
		}
	}
	public function onChat(PlayerChatEvent $event){
		$session = $this->main->getSession($event->getPlayer());
		if(!($session instanceof Session)){
			$event->setCancelled();
			return;
		}
		if($session->onChatEvent($event) === false){
			$event->setCancelled();
		}
	}
	public function onHoldItem(PlayerItemHeldEvent $event){
		$session = $this->main->getSession($event->getPlayer());
		if(!($session instanceof Session)){
			$event->setCancelled();
			return;
		}
		if($session->onHoldItem($event) === false){
			$event->setCancelled();
		}
	}
	public function onTeleport(EntityTeleportEvent $event){
		$ent = $event->getEntity();
		if(!($ent instanceof Player)){
			return;
		}
		$session = $this->main->getSession($ent);
		if(!($session instanceof Session)){
			$event->setCancelled();
			return;
		}
		if($session->onTeleport($event) === false){
			$event->setCancelled();
		}
	}
	public function onQuit(PlayerQuitEvent $event){
		$this->main->endSession($event->getPlayer());
		$event->setQuitMessage("");
	}
	public function onLogin(PlayerLoginEvent $event){
		$class = new \ReflectionClass(Player::class);
		$property = $class->getProperty("windows");
		$property->setAccessible(true);
		$windows = $property->getValue($event->getPlayer());
		var_dump($windows);
	}
}
