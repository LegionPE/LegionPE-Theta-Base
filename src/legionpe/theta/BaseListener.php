<?php

/**
 * LegionPE
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
	public function onPreLogin(PlayerPreLoginEvent $event){
		$player = $event->getPlayer();
		$this->priv_onPreLogin($player);
	}
	private function priv_onPreLogin(Player $player){
		/** @var string|LoginDataQuery $LoginQuery */
		$LoginQuery = $this->main->getLoginQueryImpl();
		/** @noinspection PhpDeprecationInspection */
		new $LoginQuery($this->main, $player->getId(), $player->getName(), $player->getAddress(), $player->getClientId());
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
	protected function addExtras(&$extra){}
}
