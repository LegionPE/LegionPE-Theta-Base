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

namespace legionpe\theta\miscellaneous\walkingparticle;

use legionpe\theta\BasePlugin;
use legionpe\theta\config\Settings;
use legionpe\theta\Session;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\EnchantmentTableParticle;
use pocketmine\level\particle\HeartParticle;

abstract class WalkingParticle{
	CONST TYPE_LAVA_DRIP = 0, TYPE_CRITICAL = 1, TYPE_ENCHANTING_TABLE = 2, TYPE_FLAME = 3, TYPE_HEART = 4, TYPE_INK = 5, TYPE_RAINBOW = 6, TYPE_RAIN_SPLASH = 7, TYPE_TOWN_AURA = 8, TYPE_WALKING = 9;
	public static $perms = [
		self::TYPE_LAVA_DRIP=>Settings::RANK_IMPORTANCE_DONATOR,
		self::TYPE_CRITICAL=>Settings::RANK_IMPORTANCE_VIP,
		self::TYPE_ENCHANTING_TABLE=>Settings::RANK_IMPORTANCE_VIP_PLUS,
		self::TYPE_FLAME=>Settings::RANK_IMPORTANCE_VIP,
		self::TYPE_HEART=>Settings::RANK_IMPORTANCE_VIP_PLUS,
		self::TYPE_INK=>Settings::RANK_IMPORTANCE_DONATOR,
		self::TYPE_RAINBOW=>Settings::RANK_IMPORTANCE_VIP_PLUS,
		self::TYPE_RAIN_SPLASH=>Settings::RANK_IMPORTANCE_DONATOR,
		self::TYPE_TOWN_AURA=>Settings::RANK_IMPORTANCE_DONATOR,
		self::TYPE_WALKING=>Settings::RANK_IMPORTANCE_DONATOR
	];
	public static $ids = [
		"lavadrip"=>self::TYPE_LAVA_DRIP,
		"critical"=>self::TYPE_CRITICAL,
		"enchantingtable"=>self::TYPE_ENCHANTING_TABLE,
		"flame"=>self::TYPE_FLAME,
		"heart"=>self::TYPE_HEART,
		"ink"=>self::TYPE_INK,
		"rainbow"=>self::TYPE_RAINBOW,
		"rainsplash"=>self::TYPE_RAIN_SPLASH,
		"townaura"=>self::TYPE_TOWN_AURA
	];
	public static $classes = [
		self::TYPE_LAVA_DRIP=>LavaDrippingWalkingParticle::class,
		self::TYPE_CRITICAL=>CriticalWalkingParticle::class,
		self::TYPE_ENCHANTING_TABLE=>EnchantingWalkingParticle::class,
		self::TYPE_FLAME=>FlameWalkingParticle::class,
		self::TYPE_HEART=>HeartParticle::class,
		self::TYPE_INK=>InkWalkingParticle::class,
		self::TYPE_RAINBOW=>RainbowWalkingParticle::class,
		self::TYPE_RAIN_SPLASH=>RainSplashWalkingParticle::class,
		self::TYPE_TOWN_AURA=>TownAuraWalkingParticle::class,
	];
	private static $nextId = 0;
	/** @var BasePlugin */
	private $plugin;
	/** @var Session */
	private $session;
	/** @var int */
	private $id;
	/** @var int */
	protected $tid;
	/** @var string */
	private $name;
	/** @var string[] */
	private $colors = [];
	public abstract function createParticles();

	public function __construct(Session $session){
		$this->session = $session;
	}
	/**
	 * @return Session
	 */
	protected function getSession(){
		return $this->session;
	}
	public function getTypeId(){
		return $this->tid;
	}
	/**
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}
	/**
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}
	/**
	 * @param string $name
	 */
	protected function setName($name){
		$this->name = $name;
	}
	/**
	 * @return string[]
	 */
	public function getColors(){
		return $this->colors;
	}
	/**
	 * @param string[] $colors
	 */
	protected function setColors($colors){
		$this->colors = $colors;
	}
	/**
	 * @param Session $session
	 * @param $id
	 * @return bool
	 */
	public static function canUseWalkingParticle(Session $session, $id){
		$rank = $session->getRank();
		if($rank & self::$perms[$id]){
			return true;
		}
		return false;
	}
	/**
	 * @param Session $session
	 * @return string
	 */
	public static function getAllowedWalkingParticleNames(Session $session){
		$names = [];
		foreach(self::$ids as $name=>$id){
			if(self::canUseWalkingParticle($session, $id)){
				$names[] = $name;
			}
		}
		return implode(", ", $names);
	}
}
