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

namespace legionpe\theta\config;

use legionpe\theta\lang\Phrases;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;

abstract class Settings{
	const STATUS_OFFLINE = 0;
	const STATUS_ONLINE = 1;
	const STATUS_TRANSFERRING = 2;
	const KICK_PLAYER_TOO_LONG_ONLINE = 5400;
	const KICK_PLAYER_TOO_LONG_LOGIN = 30;
	const POST_ONLINE_FREQUENCY = 20;
	const CLASS_ALL = 0;
	const CLASS_HUB = 1;
	const CLASS_KITPVP = 2;
	const CLASS_PARKOUR = 3;
	const CLASS_SPLEEF = 4;
	const CLASS_INFECTED = 5;
	const CLASS_CLASSICAL = 6;
	const CLASS_SHOPS = 7;
	const CLASS_CASTLE_WARS = 8;
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
	const RANK_PRECISION_STD = 0x0000;
	const RANK_PRECISION_TRIAL = 0x1000; // 16
	const RANK_PRECISION_HEAD = 0x2000; // 48
	const RANK_SECTOR_PRECISION = 0x3000; // 112
	const RANK_DECOR_YOUTUBER = 0x4000; // 240
	const RANK_SECTOR_DECOR = 0xC000; // 256
	const CONFIG_DEFAULT_VALUE = Settings::CONFIG_AUTH_UUID | Settings::CONFIG_LOCAL_CHAT_ON | Settings::CONFIG_TEAM_CHANNEL_ON | Settings::CONFIG_STATS_PUBLIC | Settings::CONFIG_TAG_ON;
	const CONFIG_SECTOR_AUTH = 0x0F; // 1024
	/** @deprecated */
	const CONFIG_AUTH_SUBNET_HISTORY = 0; // 2048
	const CONFIG_AUTH_SUBNET_LAST = 1;
	const CONFIG_AUTH_IP_HISTORY = 2;
	const CONFIG_AUTH_IP_LAST = 3;
	const CONFIG_AUTH_UUID = 4;
	const CONFIG_AUTH_NONE = 5;
	const CONFIG_TAG_ON = 0x10;
	const CONFIG_STATS_PUBLIC = 0x20;
	const CONFIG_TEAM_CHANNEL_ON = 0x40;
	const CONFIG_LOCAL_CHAT_ON = 0x80;
	const CONFIG_CLASS_CHAT_ON = 0x100;
	const TEAM_CONFIG_OPEN = 0x01;
	const LABEL_APPROVED_NOT = 0;
	const LABEL_APPROVED_EVERYONE = 1;
	const LABEL_APPROVED_DONATOR = 2;
	const LABEL_APPROVED_VIP = 3;
	const LABEL_APPROVED_MOD = 4;
	const LABEL_APPROVED_ADMIN = 5;
	const LABEL_APPROVED_REJECTED = -1;
	const LABEL_APPROVED_REJECTED_ALT = 32;
	const TEAM_RANK_JUNIOR = 0;
	const TEAM_RANK_MEMBER = 1;
	const TEAM_RANK_SENIOR = 2;
	const TEAM_RANK_COLEAD = 3;
	const TEAM_RANK_CO_LEADER = 3;
	const TEAM_RANK_LEADER = 4;
	public static $TEAM_RANKS = [
		self::TEAM_RANK_JUNIOR => "Junior-Member",
		self::TEAM_RANK_MEMBER => "Member",
		self::TEAM_RANK_SENIOR => "Senior-Member",
		self::TEAM_RANK_COLEAD => "Co-Leader",
		self::TEAM_RANK_LEADER => "Leader",
	];
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
		"shops" => self::CLASS_SHOPS,
		"castlewars" => self::CLASS_CASTLE_WARS,
	];
	/** @var string[] */
	public static $CLASSES_NAMES = [
		self::CLASS_HUB => "Hub",
		self::CLASS_KITPVP => "Kit PvP",
		self::CLASS_PARKOUR => "Parkour",
		self::CLASS_SPLEEF => "Spleef",
		self::CLASS_INFECTED => "Infected",
		self::CLASS_CLASSICAL => "Classic PvP",
		self::CLASS_SHOPS => "Shops",
		self::CLASS_CASTLE_WARS => "Castle Wars",
	];
	/** @var string[] */
	public static $CLASSES_NAMES_PHRASES = [
		self::CLASS_HUB => Phrases::CLASS_HUB,
		self::CLASS_KITPVP => Phrases::CLASS_KITPVP,
		self::CLASS_PARKOUR => Phrases::CLASS_PARKOUR,
		self::CLASS_SPLEEF => Phrases::CLASS_SPLEEF,
		self::CLASS_INFECTED => Phrases::CLASS_INFECTED,
		self::CLASS_CLASSICAL => Phrases::CLASS_CLASSIC_PVP,
		self::CLASS_SHOPS => Phrases::CLASS_SHOPS,
		self::CLASS_CASTLE_WARS => Phrases::CLASS_CASTLE_WARS,
	];
	public static function getWarnPtsConsequence(/** @noinspection PhpUnusedParameterInspection */
		$pts, $origin = null){
		$ban = 0;
		$mute = 0;
		if($pts >= 60){
			$ban = 86400 * 120;
		}elseif($pts >= 50){
			$ban = 86400 * 90;
		}elseif($pts >= 40){
			$ban = 86400 * 60;
		}elseif($pts >= 30){
			$ban = 86400 * 45;
		}elseif($pts >= 25){
			$ban = 86400 * 30;
		}elseif($pts >= 20){
			$ban = 604800;
		}elseif($pts >= 16){
			$ban = 86400 * 3;
		}elseif($pts >= 12){
			$ban = 86400;
		}elseif($pts >= 10){
			$mute = 3600;
		}elseif($pts >= 8){
			$mute = 1800;
		}elseif($pts >= 5){
			$mute = 900;
		}elseif($pts >= 3){
			$mute = 300;
		}
		return new WarnPtsConsequence($mute, $ban, $origin);
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
	/**
	 * @param Vector3 $from
	 * @param Vector3 $to
	 * @return mixed
	 */
	public static function isLocalChat($from, $to){
		return $from->distanceSquared($to) <= 1600;
	}

	public static function getMaxTeamMembers(){
		return 20;
	}
}

$config = new Config("legionpe.yml", Config::YAML, [
	"localize" => [
		"id" => 0,
		"ip" => "pe.legionpvp.eu",
		"port" => 19132,
		"class" => "hub", # hub, pvp, parkour, spleef, infected, classical, shops
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
	"classical" => Settings::CLASS_CLASSICAL,
	"shop" => Settings::CLASS_SHOPS,
	"shops" => Settings::CLASS_SHOPS,
	"castlewars" => Settings::CLASS_CASTLE_WARS,
];
if(!isset($array[$config->getNested("localize.class")])){
	throw new \RuntimeException("Invalid class: " . var_export($config->getNested("localize.class"), true));
}
Settings::$LOCALIZE_CLASS = $array[$config->getNested("localize.class")];
