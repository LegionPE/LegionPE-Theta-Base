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

namespace legionpe\theta\entity;

use legionpe\theta\BasePlugin;
use pocketmine\entity\Entity;
use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\level\ChunkUnloadEvent;
use pocketmine\event\Listener;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

class CustomEntityManager implements Listener{
	/** @var BasePlugin */
	private $main;
	/** @var Level */
	private $level;
	private $nextId = 0;
	/** @var RegisteredSpawn[][] */
	private $locs = [];
	/** @var Entity[] */
	private $spawns = [];
	public function __construct(BasePlugin $main, Level $level){
		$this->main = $main;
		$this->level = $level;
		$main->getServer()->getPluginManager()->registerEvents($this, $main);
	}
	/**
	 * @param string|Entity|CustomEntity $class
	 * @param Vector3 $spawnPos
	 * @param $data
	 */
	public function addCustomEntity($class, Vector3 $spawnPos, $data){
		$key = Level::chunkHash($spawnPos->getFloorX() >> 4, $spawnPos->getFloorZ() >> 4);
		$id = $this->nextId++;
		$this->locs[$key][$id] = new RegisteredSpawn($spawnPos->x, $spawnPos->y, $spawnPos->z, $id, $class, $data);
	}
	public function onLoad(ChunkLoadEvent $event){
		if($event->getLevel() === $this->level){
			foreach($this->locs[Level::chunkHash($event->getChunk()->getX(), $event->getChunk()->getZ())] as $id => $spawn){
				/** @var string|Entity|CustomEntity $class */
				$class = $spawn->getClass();
				/** @var Entity|CustomEntity $ent */
				$this->spawns[$id] = $ent = new $class($spawn, $spawn->getData(), $id, $this->level);
				$ent->spawnToAll();
			}
		}
	}
	public function onUnload(ChunkUnloadEvent $event){
		if($event->getLevel() === $this->level){
			foreach($this->locs[Level::chunkHash($event->getChunk()->getX(), $event->getChunk()->getZ())] as $id => $spawn){
				if(isset($this->spawns[$spawn->getId()])){
					$this->level->removeEntity($this->spawns[$spawn->getId()]);
				}
			}
		}
	}
}
