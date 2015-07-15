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

namespace legionpe\theta\utils;

use legionpe\theta\BasePlugin;
use pocketmine\scheduler\PluginTask;

class CallbackPluginTask extends PluginTask{
	/** @var callable */
	private $callable;
	/** @var mixed[] */
	private $args;
	public function __construct(BasePlugin $plugin, callable $callable, ...$args){
		parent::__construct($plugin);
		$this->callable = $callable;
		$this->args = $args;
	}
	public function onRun($t){
		$c = $this->callable;
		$c(...$this->args);
	}
}
