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

use pocketmine\entity\Human;
use pocketmine\inventory\PlayerInventory;
use pocketmine\level\format\FullChunk;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Short;

abstract class CustomHuman extends Human{ // implements CustomEntity{
	private $data;
	/**
	 * @param Vector3 $v
	 * @param mixed $data
	 * @param int $id
	 * @param FullChunk $chunk
	 */
	public function __construct($v, $data, $id = -2, $chunk = null){
		$this->data = $data;
		if(!($v instanceof Vector3)){
			if($v instanceof FullChunk){
				$v->removeEntity($this);
			}
			throw new \RuntimeException("BadConstructCustomHuman");
		}
		$nbt = new Compound;
		$nbt->Pos = new Enum("Motion", [
			new Double(0, $v->x),
			new Double(1, $v->y),
			new Double(2, $v->z),
		]);
		$nbt->Motion = new Enum("Motion", [
			new Double(0, 0),
			new Double(1, 0),
			new Double(2, 0)
		]);
		$nbt->Rotation = new Enum("Rotation", [
			new Float(0, $this->getDefaultYaw()),
			new Float(1, $this->getDefaultPitch())
		]);
		$nbt->FallDistance = new Float("FallDistance", 0);
		$nbt->Fire = new Short("Fire", 0);
		$nbt->Air = new Short("Air", 0);
		$nbt->OnGround = new Byte("OnGround", 1);
		$nbt->Invulnerable = new Byte("Invulnerable", 1);
		$nbt->Health = new Short("Health", 20);
		$nbt->NameTag = $this->getDefaultName();
		$nbt->Inventory = new Enum("Inventory", []);
		$nbt->Inventory->setTagType(NBT::TAG_Compound);
		$this->inventory = new PlayerInventory($this);
		$this->setSkin($this->getDefaultSkin());
		parent::__construct($chunk, $nbt);
	}
	public abstract function getDefaultSkin();
	public abstract function getDefaultYaw();
	public abstract function getDefaultPitch();
	public abstract function getDefaultName();
}
