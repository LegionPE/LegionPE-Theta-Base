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

namespace legionpe\theta\config;

use pocketmine\utils\Config;

abstract class Settings{
	const STATUS_OFFLINE = 0;
	const STATUS_ONLINE = 1;
	const STATUS_TRANSFERRING = 2;

	public static $SYSTEM_MAX_PLAYERS;
	public static $SYSTEM_IS_TEST;
	public static $PROCESS_ID;
	public static $LOCALIZE_ID;
	public static $LOCALIZE_IP;
	public static $LOCALIZE_PORT;
	public static $LOCALIZE_CLASS;
	/** @var int[] */
	public static $CLASSES_TABLE = [
		"hub" => self::CLASS_HUB,
		"pvp" => self::CLASS_PVP,
		"parkour" => self::CLASS_PARKOUR,
		"spleef" => self::CLASS_SPLEEF,
		"infected" => self::CLASS_INFECTED,
		"classical" => self::CLASS_CLASSICAL,
	];
	public static $CLASSES_NAMES = [
		self::CLASS_HUB => "Hub",
		self::CLASS_PVP => "Kit PvP",
		self::CLASS_PARKOUR => "Parkour",
		self::CLASS_SPLEEF => "Spleef",
		self::CLASS_INFECTED => "Infected",
		self::CLASS_CLASSICAL => "Classic PvP"
	];
	const CLASS_HUB = 0;
	const CLASS_PVP = 1;
	const CLASS_PARKOUR = 2;
	const CLASS_SPLEEF = 3;
	const CLASS_INFECTED = 4;
	const CLASS_CLASSICAL = 5;

	// ranks of importance (how important the person is, like VERY Important Person) must not exceed 15 according to this, 1 nibble
	// the first two bits are the two actual permission-affecting nibbles
	const RANK_IMPORTANCE_DEFAULT =         0x0000; // 0  , 0000
	const RANK_IMPORTANCE_TESTER =          0x0001; // 1  , 0001
	const RANK_IMPORTANCE_DONATOR =         0x0004; // 4  , 0100
	const RANK_IMPORTANCE_DONATOR_PLUS =    0x0005; // 5  , 0101
	const RANK_IMPORTANCE_VIP =             0x000C; // 12 , 1100
	const RANK_IMPORTANCE_VIP_PLUS =        0x000D; // 13 , 1101
	const RANK_SECTOR_IMPORTANCE =          0x000F;
	// permissions the rank has, 2 nibbles
	const RANK_PERM_DEFAULT =               0x0000;
	const RANK_PERM_MOD =                   0x0010; // 16
	const RANK_PERM_ADMIN =                 0x0030; // 48
	const RANK_PERM_OWNER =                 0x0070; // 112
	const RANK_PERM_STAFF =                 0x00F0; // 240
	/** Permission to be undetected by the auto AFK kicker. */
	const RANK_PERM_AFK =                   0x0100; // 256
	/** Permission to bypass spam (spam detector won't detect at all). SpicyCapacitor ignores this permission and logs anyways. */
	const RANK_PERM_SPAM =                  0x0200; // 512
	/** Permission to edit the world. */
	const RANK_PERM_WORLD_EDIT =            0x0400; // 1024
	/** Permission to execute raw PHP code by `/eval` */
	const RANK_PERM_DEV =                   0x0800; // 2048
	const RANK_SECTOR_PERMISSION =          0x00F0;
	// precise (generally won't affect the program) degree of rank, 2 bits
	const RANK_PREC_STD =                   0x0000;
	const RANK_PREC_TRIAL =                 0x1000;
	const RANK_PREC_HEAD =                  0x2000;
	const RANK_SECTOR_PRECISION =           0x3000;
	// decorative ranks, which don't actually affect anything, 2 bits
	/** Here you are, the youtuber rank */
	const RANK_DECOR_YOUTUBER =             0x4000;
	const RANK_SECTOR_DECOR =               0xC000;

	const CONFIG_SECTOR_AUTH = 0x0F;
	/** @deprecated */
	const CONFIG_AUTH_SUBNET_HISTORY = 0;
	const CONFIG_AUTH_SUBNET_LAST = 1;
	const CONFIG_AUTH_IP_HISTORY = 2;
	const CONFIG_AUTH_IP_LAST = 3;
	const CONFIG_AUTH_UUID = 4;
	const CONFIG_AUTH_NONE = 5;

	public static function getWarnPtsConseq($pts, $origin = null){
		if($pts >= 120){
			return new WarnPtsConseq(0, 7776000, $origin);
		}
		if($pts >= 90){
			return new WarnPtsConseq(0, 5184000, $origin);
		}
		if($pts >= 60){
			return new WarnPtsConseq(0, 3888000, $origin);
		}
		if($pts >= 45){
			return new WarnPtsConseq(0, 2592000, $origin);
		}
		if($pts >= 30){
			return new WarnPtsConseq(0, 604800, $origin);
		}
		if($pts >= 25){
			return new WarnPtsConseq(0, 259200, $origin);
		}
		if($pts >= 20){
			return new WarnPtsConseq(0, 86400, $origin);
		}
		if($pts >= 15){
			return new WarnPtsConseq(3600, 0, $origin);
		}
		if($pts >= 10){
			return new WarnPtsConseq(1800, 0, $origin);
		}
		if($pts >= 7){
			return new WarnPtsConseq(900, 0, $origin);
		}
		if($pts >= 4){
			return new WarnPtsConseq(600, 0, $origin);
		}
		if($pts >= 1){
			return new WarnPtsConseq(300, 0, $origin);
		}
		return new WarnPtsConseq;
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
Settings::$LOCALIZE_CLASS = $config->getNested("localize.class");
