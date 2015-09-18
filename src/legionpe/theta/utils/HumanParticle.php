<?php

/*
 * LegionPE
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

namespace legionpe\theta\utils;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\PlayerArmorEquipmentPacket;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\Player;

class HumanParticle extends Vector3{
	/** @var int */
	private $eid;
	/** @var string */
	public $nameTag;
	/** @var float */
	public $yaw, $pitch;
	/** @var Item */
	public $item;
	/** @var string */
	private $skin;
	/** @var bool */
	private $slim;
	/** @var int */
	public $helmetId = 0, $chestplateId = 0, $leggingsId = 0, $bootsId = 0;
	public $metadata = [];
	/** @var Player */
	private $player;
	public function __construct($x, $y, $z, $nameTag, $yaw, $pitch, Item $item, $skin, $slim = false){
		parent::__construct($x, $y, $z);
		$this->nameTag = $nameTag;
		$this->yaw = $yaw;
		$this->pitch = $pitch;
		$this->item = $item;
		$this->skin = $skin;
		$this->slim = $slim;
		$this->metadata = [
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 0],
			Entity::DATA_AIR => [Entity::DATA_TYPE_SHORT, 300],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->nameTag],
			Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
			Entity::DATA_SILENT => [Entity::DATA_TYPE_BYTE, 1],
			Entity::DATA_NO_AI => [Entity::DATA_TYPE_BYTE, 1],
		];
	}
	public function spawnTo(Player $player){
		$app = new AddPlayerPacket;
		$app->clientID = $this->eid();
		$app->username = $this->nameTag;
		$app->eid = $this->eid();
		$app->x = $this->x;
		$app->y = $this->y;
		$app->z = $this->z;
		$app->speedX = 0;
		$app->speedY = 0;
		$app->speedZ = 0;
		$app->yaw = $this->yaw;
		$app->pitch = $this->pitch;
		$app->item = $this->item->getId();
		$app->meta = $this->item->getDamage();
		$app->skin = $this->skin;
		$app->slim = $this->slim;
		$app->metadata = $this->metadata;
		$player->dataPacket($app);
		$this->player = $player;
		$this->setDataFlag(Human::DATA_PLAYER_FLAGS, Human::DATA_PLAYER_FLAG_SLEEP, false);
		$this->setDataProperty(Human::DATA_PLAYER_BED_POSITION, Entity::DATA_TYPE_POS, [0, 0, 0]);
	}
	public function sendArmor(){
		$aep = new PlayerArmorEquipmentPacket;
		$aep->eid = $this->eid();
		$aep->slots = [];
		$aep->slots[0] = ($this->helmetId === 0) ? 255 : $this->helmetId;
		$aep->slots[1] = ($this->chestplateId === 0) ? 255 : $this->chestplateId;
		$aep->slots[2] = ($this->leggingsId === 0) ? 255 : $this->leggingsId;
		$aep->slots[3] = ($this->bootsId === 0) ? 255 : $this->bootsId;
		$this->player->dataPacket($aep);
	}
	public function setName($name){
		$this->setDataProperty(Entity::DATA_NAMETAG, Entity::DATA_TYPE_STRING, $name);
	}
	public function setOnFire($bool){
		$this->setDataFlag(Entity::DATA_FLAG_ONFIRE, $bool);
	}
	public function setSneak($bool){
		$this->setDataFlag(Entity::DATA_FLAG_SNEAKING, $bool);
	}
	public function setInAction($bool){
		$this->setDataFlag(Entity::DATA_FLAG_ACTION, $bool);
	}
	public function setInvisible($bool){
		$this->setDataFlag(Entity::DATA_FLAG_INVISIBLE, $bool);
	}
	public function setRiding($bool){
		$this->setDataFlag(Entity::DATA_FLAG_RIDING, $bool);
	}
	private function setDataProperty($id, $type, $value){
		if($this->getDataProperty($id) !== $value){
			$this->metadata[$id] = [$type, $value];
			$this->sendData([$id => $this->metadata[$id]]);
		}
	}
	private function getDataProperty($id){
		return isset($this->metadata[$id]) ? $this->metadata[$id][1] : null;
	}
	private function setDataFlag($id, $value = true, $type = Entity::DATA_TYPE_BYTE){
		if($this->getDataFlag($id) !== $value){
			$flags = (int) $this->getDataProperty(Entity::DATA_FLAGS);
			$flags ^= 1 << $id;
			$this->setDataProperty(Entity::DATA_FLAGS, $type, $flags);
		}
	}
	private function getDataFlag($id){
		return (((int) $this->getDataProperty(Entity::DATA_FLAGS)) & (1 << $id)) > 0;
	}
	private function sendData($data){
		$pk = new SetEntityDataPacket;
		$pk->eid = $this->eid();
		$pk->metadata = $data === null ? $this->metadata : $data;
		$this->player->dataPacket($pk);
	}
	private function eid(){
		if(!isset($this->eid)){
			$this->eid = Entity::$entityCount++;
		}
		return $this->eid;
	}
}
