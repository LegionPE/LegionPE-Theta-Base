<?php

/**
 * LegionPE-Theta
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

namespace legionpe\theta\credentials;

/** @noinspection PhpUndefinedClassInspection */
class Credentials{
	const MYSQL_HOST = "****";
	const MYSQL_USER = "****";
	const MYSQL_PASS = "****";
	const MYSQL_DATABASE = "****";
	const MYSQL_PORT = 3306;
	const IRC_WEBHOOK = "http://n.tkte.ch/h/****/***********?payload=%7BLegionPE+Error%21%7D+";
	const IRC_WEBHOOK_NOPREFIX = "http://n.tkte.ch/h/****/***********?payload=";
	const IRC_WEBHOOK_STATUS = "http://n.tkte.ch/h/****/***********?payload=";
	const REPO_TOKEN = "****GitHub OAuth Access Token****";
	/**
	 * @return \mysqli
	 */
	public static function getMysql(){
	}
}
