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
