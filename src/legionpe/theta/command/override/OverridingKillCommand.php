<?php

/*
 *
 *    _____          _     _ _
 *   / ___ \ __   __(_) __| (_)____ ___ _ ___
 *  | /   \ |\ \_/ /| |/ /| | |_  // _ | v __)
 *  | \___/ |/ _  / | | ()| | |/ /|  __| /
 *   \_____//_/ \_\ |_|\__/\|_|____\___|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PEMapModder Team
 * @link http://legendofmcpe.github.io/oxidizer/
 *
 */

namespace legionpe\theta\command\override;

use legionpe\theta\command\SessionCommand;
use legionpe\theta\Session;
use pocketmine\event\entity\EntityDamageEvent;

class OverridingKillCommand extends SessionCommand{
	protected function run(array $args, Session $sender){
		$sender->getPlayer()->attack($sender->getPlayer()->getHealth(), new EntityDamageEvent($sender->getPlayer(), EntityDamageEvent::CAUSE_SUICIDE, 20000));
	}
}
