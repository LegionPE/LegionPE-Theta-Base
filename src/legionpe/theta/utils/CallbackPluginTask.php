<?php

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
