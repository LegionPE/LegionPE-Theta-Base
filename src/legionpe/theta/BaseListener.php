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

use legionpe\theta\config\Settings;
use legionpe\theta\query\LoginDataQuery;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class BaseListener implements Listener{
	/** @var BasePlugin */
	private $main;
	public function __construct(BasePlugin $main){
		$this->main = $main;
		foreach($main->getServer()->getOnlinePlayers() as $player){
			$this->priv_onPreLogin($player);
		}
	}
	private function priv_onPreLogin(Player $player){
		/** @var string|LoginDataQuery $LoginQuery */
		$LoginQuery = $this->main->getLoginQueryImpl();
		/** @noinspection PhpDeprecationInspection */
		new $LoginQuery($this->main, $player->getId(), $player->getName(), $player->getAddress(), $player->getClientId());
	}
	public function onPreLogin(PlayerPreLoginEvent $event){
		$player = $event->getPlayer();
		$this->priv_onPreLogin($player);
	}
	public function onQueryRegen(QueryRegenerateEvent $event){
		$event->setWorld($this->main->query_world());
		$this->main->getPlayersCount($total, $max, $classTotal, $classMax);
		$event->setPlayerCount($classTotal);
		$event->setMaxPlayerCount($classMax);
		$event->setPlayerList([]);
		$event->setServerName(TextFormat::clean($this->main->getServer()->getNetwork()->getName()));
		$extra = $event->getExtraData();
		$name = strtolower(Settings::$CLASSES_NAMES[Settings::$LOCALIZE_CLASS]);
		$extra[$name . "_numplayers"] = $classTotal;
		$extra[$name . "_maxplayers"] = $classMax;
		$this->addExtras($extra);
		$event->setExtraData($extra);
	}
	protected function addExtras(&$extra){
	}
}
