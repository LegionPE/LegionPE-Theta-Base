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

use legionpe\theta\query\LoginDataQuery;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\plugin\PluginDisableEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class BaseListener implements Listener{
	/** @var BasePlugin */
	private $main;
	public function __construct(BasePlugin $main){
		$this->main = $main;
		foreach($main->getServer()->getOnlinePlayers() as $player){
			$this->private_onLogin($player);
		}
	}
	private function private_onLogin(Player $player){
		/** @var string|LoginDataQuery $LoginQuery */
		$LoginQuery = $this->main->getLoginQueryImpl();
		/** @noinspection PhpDeprecationInspection */
		new $LoginQuery($this->main, $player->getId(), $player->getName(), $player->getAddress(), $player->getClientId());
	}
	public function onPacketRecv(DataPacketReceiveEvent $event){
//		if($event->getPacket()->pid() === OldLoginPacket::NETWORK_ID){
//			$pk = new TransferPacket;
//			$pk->address = $this->getMain()->getHostByName("pe.legionpvp.eu");
//			$pk->port = 19131;
//			$event->getPlayer()->dataPacket($pk);
//		}
	}
	public function onPreLogin(PlayerPreLoginEvent $event){
		$this->getMain()->newJoins++;
		$player = $event->getPlayer();
		foreach($this->main->getServer()->getOnlinePlayers() as $other){
			if($other === $player or strtolower($other->getName()) !== strtolower($player->getName())){
				continue;
			}
			/** @noinspection PhpDeprecationInspection */
			if($other->getRawUniqueId() === $player->getRawUniqueId()){
				$other->close("You rejoined from the same IP with the same client and the same username.");
			}else{
				$event->setCancelled();
				$event->setKickMessage("A player of this username has already connected from a different IP of yours or from another client.");
				return;
			}
		}
	}
	public function onLogin(PlayerLoginEvent $event){
		$this->private_onLogin($event->getPlayer());
	}
	public function onQueryRegen(QueryRegenerateEvent $event){
		/*$event->setWorld($this->main->query_world());
		$this->main->getPlayersCount($total, $max, $classTotal, $classMax);
		$event->setPlayerCount($classTotal);
		$event->setMaxPlayerCount($classMax);
		$event->setPlayerList([]);
		$event->setServerName(TextFormat::clean($this->main->getServer()->getNetwork()->getName()));
		$extra = $event->getExtraData();
		$extra["class_numplayers"] = $classMax;
		$extra["class_maxplayers"] = $classMax;
		$this->addExtras($extra);
		$event->setExtraData($extra);*/
		$event->setServerName("§aLegionPE Theta§7: §cClassic PvP (temp)");
		$event->setPlayerCount(count($this->main->getServer()->getOnlinePlayers()));
		$event->setMaxPlayerCount($this->main->getServer()->getMaxPlayers());
	}
	protected function addExtras(&$extra){
	}
	public function onKick(PlayerKickEvent $event){
		if($event->getReason() === "disconnectionScreen.serverFull"){
			$event->setCancelled();
			$this->main->getAltServer($ip, $port);
			if($ip !== "0.0.0.0"){
				$this->getMain()->getLogger()->notice("Transferring " . $event->getPlayer()->getName() . " to $ip:$port because server strict limit is reached");
				$this->main->transfer($event->getPlayer(), $ip, $port, "This server is full");
			}
		}
	}
//	public function onLoadChunk(ChunkLoadEvent $event){
//		for($i = 0; $i < 16; $i++){
//			for($j = 0; $j < 16; $j++){
//				$event->getChunk()->setBiomeColor($i, $j, 141 + mt_rand(-8, 8), 179 + mt_rand(-8, 8), 96 + mt_rand(-8, 8));
//				$event->getChunk()->setChanged(true);
//			}
//		}
//	}
	public function onDisable(PluginDisableEvent $event){
		if($event->getPlugin() === $this->main){
			foreach($this->main->getServer()->getOnlinePlayers() as $player){
				$player->kick("Server stop", false);
			}
		}
	}
	/**
	 * @return BasePlugin
	 */
	public function getMain(){
		return $this->main;
	}
}
