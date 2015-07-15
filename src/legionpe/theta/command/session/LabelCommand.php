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
