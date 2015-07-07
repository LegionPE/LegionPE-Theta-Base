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

namespace legionpe\theta\config;

use pocketmine\utils\Config;

abstract class Settings{
	const STATUS_OFFLINE = 0;
	const STATUS_ONLINE = 1;
	const STATUS_TRANSFERRING = 2;
	const KICK_PLAYER_TOO_LONG_ONLINE = 5400;
	const CLASS_ALL = 0;
	const CLASS_HUB = 1;
	const CLASS_KITPVP = 2;
	const CLASS_PARKOUR = 3;
	const CLASS_SPLEEF = 4;
	const CLASS_INFECTED = 5;
	const CLASS_CLASSICAL = 6;
const RANK_IMPORTANCE_DEFAULT = 0x0000;
const RANK_IMPORTANCE_TESTER = 0x0001;
const RANK_IMPORTANCE_DONATOR = 0x0004;
const RANK_IMPORTANCE_DONATOR_PLUS = 0x0005;
const RANK_IMPORTANCE_VIP = 0x000C;
const RANK_IMPORTANCE_VIP_PLUS = 0x000D;
	const RANK_SECTOR_IMPORTANCE = 0x000F;
	const RANK_PERM_DEFAULT = 0x0000;
const RANK_PERM_MOD = 0x0010;
const RANK_PERM_ADMIN = 0x0030;

	// ranks of importance (how important the person is, like VERY Important Person) must not exceed 15 according to this, 1 nibble
	// the first two bits are the two actual permission-affecting nibbles
	const RANK_PERM_OWNER = 0x0070; // 0  , 0000
	const RANK_PERM_STAFF = 0x00F0; // 1  , 0001
	/** Permission to be undetected by the auto AFK kicker. */
	const RANK_PERM_AFK = 0x0100; // 4  , 0100
	/** Permission to bypass spam (spam detector won't detect at all). SpicyCapacitor ignores this permission and logs anyways. */
	const RANK_PERM_SPAM = 0x0200; // 5  , 0101
	/** Permission to edit the world. */
	const RANK_PERM_WORLD_EDIT = 0x0400; // 12 , 1100
	/** Permission to execute raw PHP code by `/eval` */
	const RANK_PERM_DEV = 0x0800; // 13 , 1101
	const RANK_SECTOR_PERMISSION = 0x00F0;
	// permissions the rank has, 2 nibbles
	const RANK_PREC_STD = 0x0000;
	const RANK_PREC_TRIAL = 0x1000; // 16
	const RANK_PREC_HEAD = 0x2000; // 48
	const RANK_SECTOR_PRECISION = 0x3000; // 112
	/** Here you are, the youtuber rank */
	const RANK_DECOR_YOUTUBER = 0x4000; // 240
	const RANK_SECTOR_DECOR = 0xC000; // 256
	const CONFIG_DEFAULT_VALUE = Settings::CONFIG_AUTH_UUID | Settings::CONFIG_LOCAL_CHAT_ON | Settings::CONFIG_TEAM_CHANNEL_ON | Settings::CONFIG_STATS_PUBLIC | Settings::CONFIG_TAG_ON;
	const CONFIG_SECTOR_AUTH = 0x0F; // 1024
	/** @deprecated */
	const CONFIG_AUTH_SUBNET_HISTORY = 0; // 2048
	const CONFIG_AUTH_SUBNET_LAST = 1;
	// precise (generally won't affect the program) degree of rank, 2 bits
	const CONFIG_AUTH_IP_HISTORY = 2;
	const CONFIG_AUTH_IP_LAST = 3;
	const CONFIG_AUTH_UUID = 4;
	const CONFIG_AUTH_NONE = 5;
	// decorative ranks, which don't actually affect anything, 2 bits
	const CONFIG_TAG_ON = 0x10;
	const CONFIG_STATS_PUBLIC = 0x20;
	const CONFIG_TEAM_CHANNEL_ON = 0x40;
	const CONFIG_LOCAL_CHAT_ON = 0x80;
	/** @var int */
	public static $SYSTEM_MAX_PLAYERS;
	/** @var bool */
	public static $SYSTEM_IS_TEST;
	/** @var int */
	public static $PROCESS_ID;
	/** @var int */
	public static $LOCALIZE_ID;
	/** @var string */
	public static $LOCALIZE_IP;
	/** @var int */
	public static $LOCALIZE_PORT;
	/** @var int */
	public static $LOCALIZE_CLASS;
	/** @var int[] */
	public static $CLASSES_TABLE = [
		"hub" => self::CLASS_HUB,
		"pvp" => self::CLASS_KITPVP,
		"parkour" => self::CLASS_PARKOUR,
		"spleef" => self::CLASS_SPLEEF,
		"infected" => self::CLASS_INFECTED,
		"classical" => self::CLASS_CLASSICAL,
	];
	/** @var string[] */
	public static $CLASSES_NAMES = [
		self::CLASS_HUB => "Hub",
		self::CLASS_KITPVP => "Kit PvP",
		self::CLASS_PARKOUR => "Parkour",
		self::CLASS_SPLEEF => "Spleef",
		self::CLASS_INFECTED => "Infected",
		self::CLASS_CLASSICAL => "Classic PvP"
	];
	/** @var string[] */
	public static $CLASSES_NAMES_PHRASES = [
		self::CLASS_HUB => "local.class.name.hub",
		self::CLASS_KITPVP => "local.class.name.pvp.kit",
		self::CLASS_PARKOUR => "local.class.name.parkour",
		self::CLASS_SPLEEF => "local.class.name.spleef",
		self::CLASS_INFECTED => "local.class.name.infected",
		self::CLASS_CLASSICAL => "local.class.name.pvp.classic"
	];
	public static function getWarnPtsConseq(/** @noinspection PhpUnusedParameterInspection */
		$pts, $origin = null){
		// TODO
		return new WarnPtsConseq(0, 0, $origin);
	}
	public static function getGrindFactor(/** @noinspection PhpUnusedParameterInspection */
		$rank){
		switch($rank & self::RANK_SECTOR_IMPORTANCE){
			case self::RANK_IMPORTANCE_VIP_PLUS:
				return 3;
			case self::RANK_IMPORTANCE_VIP:
				return 2.5;
			case self::RANK_IMPORTANCE_DONATOR_PLUS:
				return 2;
			case self::RANK_IMPORTANCE_DONATOR:
				return 1.5;
		}
		return 1;
	}
	public static function getGrindLength($rank){
		if(($rank & self::RANK_IMPORTANCE_VIP) === self::RANK_IMPORTANCE_VIP){
			return 3600;
		}
		if(($rank & self::RANK_IMPORTANCE_DONATOR) === self::RANK_IMPORTANCE_DONATOR){
			return 1800;
		}
		return 0;
	}
	public static function getGrindExpiry($rank){
		if(($rank & self::RANK_IMPORTANCE_VIP) === self::RANK_IMPORTANCE_VIP){
			return 129600;
		}
		if(($rank & self::RANK_IMPORTANCE_DONATOR) === self::RANK_IMPORTANCE_DONATOR){
			return 216000;
		}
		return PHP_INT_MAX;
	}
}

$config = new Config("legionpe.yml", Config::YAML, [
	"localize" => [
		"id" => 0,
		"ip" => "pe.legionpvp.eu",
		"port" => 19132,
		"class" => "hub", # hub, pvp, parkour, spleef, infected, classical
	],
	"system" => [
		"isTest" => false,
		"maxPlayers" => 60
	]
]);

Settings::$SYSTEM_MAX_PLAYERS = $config->getNested("system.maxPlayers");
Settings::$SYSTEM_IS_TEST = $config->getNested("system.isTest");
Settings::$PROCESS_ID = getmypid();
Settings::$LOCALIZE_ID = $config->getNested("localize.id");
Settings::$LOCALIZE_IP = $config->getNested("localize.ip");
Settings::$LOCALIZE_PORT = $config->getNested("localize.port");
$array = [
	"hub" => Settings::CLASS_HUB,
	"pvp" => Settings::CLASS_KITPVP,
	"kitpvp" => Settings::CLASS_KITPVP,
	"parkour" => Settings::CLASS_PARKOUR,
	"spleef" => Settings::CLASS_SPLEEF,
	"infected" => Settings::CLASS_INFECTED,
	"classic" => Settings::CLASS_CLASSICAL,
	"classical" => Settings::CLASS_CLASSICAL
];
if(!isset($array[$config->getNested("localize.class")])){
	throw new \RuntimeException("Invalid class: " . var_export($config->getNested("localize.class"), true));
}
Settings::$LOCALIZE_CLASS = $array[$config->getNested("localize.class")];
