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
	private static $nextId = 0;
	/** @var BasePlugin */
	private $plugin;
	/** @var Session */
	private $session;
	/** @var int */
	private $id;
	/** @var string */
	private $name;
	/** @var string[] */
	private $colors = [];
	public abstract function createParticles();

	public function __construct(BasePlugin $plugin, Session $session){
		$plugin->walkingParticles[$this->id = self::$nextId++] = $this;
		$this->plugin = $plugin;
		$this->session = $session;
	}
	public function __destruct(){
		unset($this->plugin->walkingParticles[$this->id]);
	}
	/**
	 * @return Session
	 */
	protected function getSession(){
		return $this->session;
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
