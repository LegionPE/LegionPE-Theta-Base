<?php

/**
 * Theta
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
use legionpe\theta\lang\Phrases;
use legionpe\theta\query\FetchLabelQuery;
use legionpe\theta\Session;

class LabelCommand extends SessionCommand{
	private static $APPROVAL_LEVELS = [
		Settings::LABEL_APPROVED_NOT => "Pending for approval",
		Settings::LABEL_APPROVED_REJECTED => "Rejected",
		Settings::LABEL_APPROVED_REJECTED_ALT => "Rejected",
		Settings::LABEL_APPROVED_EVERYONE => "Approved for Everyone",
		Settings::LABEL_APPROVED_DONATOR => "Approved for Donator only",
		Settings::LABEL_APPROVED_VIP => "Approved for VIP only",
		Settings::LABEL_APPROVED_MOD => "Approved for moderators only",
//		Settings::LABEL_APPROVED_ADMIN => "Approved for admins only",
	];
	public function __construct(BasePlugin $main){
		parent::__construct($main, "label", "Check/change your prefix label", "/lbl [new label]", ["lbl"]);
	}
	protected function run(array $args, Session $sender){
		if(!isset($args[0])){
			$lbl = $sender->getLoginDatum("lbl");
			$appr = $sender->getLoginDatum("lblappr");
			$sender->send(Phrases::CMD_LABEL_VIEW, [
				"label" => $lbl,
				"state" => self::$APPROVAL_LEVELS[$appr]
			]);
			return true;
		}
		$sender->send(Phrases::CMD_ERR_LOADING);
		new FetchLabelQuery($this->getPlugin(), $args[0], $sender);
		return true;
	}
	protected function checkPerm(Session $session, &$msg = null){
		if($session->isModerator() or $session->isDonator()){
			return true;
		}
		$msg = $session->translate(Phrases::CMD_ERR_NO_PERM);
		return false;
	}
}
