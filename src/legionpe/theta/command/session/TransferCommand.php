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
	protected function run(array $args, Session $session){
		if(Settings::$LOCALIZE_CLASS === $this->class){
			return TextFormat::RED . "You are already in $this->human!";
		}
		$this->getPlugin()->transferGame($session->getPlayer(), $this->class);
		return TextFormat::AQUA . "Finding an available " . MUtils::word_addSingularArticle($this->human) . " server for you...";
	}
}
