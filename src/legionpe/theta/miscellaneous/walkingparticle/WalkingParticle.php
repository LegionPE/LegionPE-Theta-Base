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
use legionpe\theta\Session;

abstract class WalkingParticle{
	CONST TYPE_LAVA_DRIP = 0, TYPE_CRITICAL = 1, TYPE_ENCHANTING_TABLE = 2, TYPE_FLAME = 3, TYPE_HEART = 4, TYPE_INK = 5, TYPE_RAINBOW = 6, TYPE_RAIN_SPLASH = 7, TYPE_TOWN_AURA = 8, TYPE_WALKING = 9;

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
}
