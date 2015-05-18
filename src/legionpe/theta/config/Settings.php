<?php

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
	const CLASS_HUB = 0;
	const CLASS_PVP = 1;
	const CLASS_PARKOUR = 2;
	const CLASS_SPLEEF = 3;
	const CLASS_INFECTED = 4;
	const CLASS_CLASSICAL = 5;
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
