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

namespace legionpe\theta\command\session;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\SessionCommand;
use legionpe\theta\config\Settings;
use legionpe\theta\Session;
use legionpe\theta\utils\MUtils;
use pocketmine\utils\TextFormat;

class TransferCommand extends SessionCommand{
	/** @var string */
	private $human;
	/** @var int */
	private $class;
	/**
	 * @param BasePlugin $plugin
	 * @param string[] $aliases
	 * @param string $human
	 * @param int $id
	 */
	public function __construct(BasePlugin $plugin, array $aliases, $human, $id){
		parent::__construct($plugin, $name = array_shift($aliases), "Transfer to " . $human, "/$name", $aliases);
		$this->human = $human;
		$this->class = $id;
	}
	protected function run(array $args, Session $sender){
		if(Settings::$LOCALIZE_CLASS === $this->class){
			return TextFormat::RED . "You are already in $this->human!";
		}
		$this->getPlugin()->transferGame($sender->getPlayer(), $this->class, !$sender->isDonator());
		return TextFormat::AQUA . "Finding an available " . MUtils::word_addSingularArticle($this->human) . " server for you...";
	}
}
