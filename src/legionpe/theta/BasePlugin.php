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

namespace legionpe\theta;

use legionpe\theta\chat\Hormone;
use legionpe\theta\command\ThetaCommand;
use legionpe\theta\config\Settings;
use legionpe\theta\credentials\Credentials;
use legionpe\theta\lang\LanguageManager;
use legionpe\theta\lang\Phrases;
use legionpe\theta\query\CloseServerQuery;
use legionpe\theta\query\InitDbQuery;
use legionpe\theta\query\LoginDataQuery;
use legionpe\theta\query\NewPrivateMessageQuery;
use legionpe\theta\query\NewUserQuery;
use legionpe\theta\query\SaveSinglePlayerQuery;
use legionpe\theta\query\TransferServerQuery;
use legionpe\theta\queue\Queue;
use legionpe\theta\utils\DbPingQuery;
use legionpe\theta\utils\FireSyncChatQueryTask;
use legionpe\theta\utils\MUtils;
use legionpe\theta\utils\RandomBroadcastTask;
use legionpe\theta\utils\ResendPlayersTask;
use legionpe\theta\utils\RestartServerTask;
use legionpe\theta\utils\SessionTickTask;
use legionpe\theta\utils\SyncStatusTask;
use libtheta\info\pvp\PvpKitInfo;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\MainLogger;
use pocketmine\utils\TextFormat;
use shoghicp\FastTransfer\FastTransfer;

const RESEND_ADD_PLAYER = 50;

abstract class BasePlugin extends PluginBase{
	const EMAIL_UNVERIFIED = "NOTSET";
	private static $NAME = null;
	private static $CLASS = null;
	/** @var SessionEventListener */
	protected $sesList;
	/** @var FastTransfer */
	private $FastTransfer;
	/** @var BaseListener */
	private $listener;
	/** @var LanguageManager */
	private $langs;
	/** @var \mysqli */
	private $db;
	/** @var int[] */
	private $faceSeeks = [];
	/** @var string[] */
	private $badWords, $approvedDomains;
	/** @var Queue[] */
	private $queues = [], $playerQueues = [], $teamQueues = [];
	/** @var Session[] */
	private $sessions = [];
	private $totalPlayers, $maxPlayers, $classTotalPlayers, $classMaxPlayers, $servers, $classServers;
	/** @var string */
	private $altIp = "0.0.0.0";
	/** @var int */
	private $altPort = 0;
	/** @var int */
	private $internalLastChatId = null;
	/** @var FireSyncChatQueryTask */
	private $syncChatTask;
	private $objectStore = [];
	private $nextStoreId = 1;
	/** @var MuteIssue[] */
	private $mutes = [];

	// PluginManager-level stuff
	/**
	 * @param Server $server
	 * @return static
	 */
	public static function getInstance(Server $server){
		return $server->getPluginManager()->getPlugin(self::$NAME);
	}
	public function onLoad(){
		self::$NAME = $this->getName();
		self::$CLASS = static::class;
		class_exists(Credentials::class);
		class_exists(CloseServerQuery::class); // preload to workaround frequent corruption errors due to phar repalced
		class_exists(SaveSinglePlayerQuery::class);
		class_exists(LoginDataQuery::class);
		if(!is_dir($this->getDataFolder())){
			mkdir($this->getDataFolder());
		}
		PvpKitInfo::init();
	}
	public function onEnable(){
		$this->langs = new LanguageManager($this);
		$this->FastTransfer = $this->getServer()->getPluginManager()->getPlugin("FastTransfer");
		ThetaCommand::registerAll($this, $this->getServer()->getCommandMap());
		$class = $this->getBasicListenerClass();
		$this->getServer()->getPluginManager()->registerEvents($this->listener = new $class($this), $this);
		$class = $this->getSessionListenerClass();
		$this->getServer()->getPluginManager()->registerEvents($this->sesList = new $class($this), $this);
		$this->db = Credentials::getMysql();
		new InitDbQuery($this);
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new SyncStatusTask($this), 40, 20);
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new SessionTickTask($this), 1, 10);
		$this->getServer()->getScheduler()->scheduleRepeatingTask($this->syncChatTask = new FireSyncChatQueryTask($this), 5);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new DbPingQuery($this), 1200);
		$this->getServer()->getScheduler()->scheduleDelayedTask(new RestartServerTask($this), 72000);
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new RandomBroadcastTask($this), 2400, 2400);
		if(RESEND_ADD_PLAYER > 0){
			$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new ResendPlayersTask($this), RESEND_ADD_PLAYER, RESEND_ADD_PLAYER);
		}
		$this->faceSeeks = json_decode($this->getResourceContents("head.json"));
		$this->badWords = json_decode($this->getResourceContents("words.json"));
		$this->approvedDomains = json_decode($this->getResourceContents("approvedDomains.json"));
		$buildInfo = json_decode($this->getResourceContents("build.json"));
		$compileTime = $buildInfo->time;
		$buildNumber = $buildInfo->buildNumber;
		$buildAuthor = $buildInfo->buildAuthor;
		$this->getLogger()->alert("Enabled " . $this->getDescription()->getFullName() . " Build $buildNumber compiled at " . date("d/m/Y H:i:s (P)", $compileTime) . " (" . MUtils::time_secsToString(time() - $compileTime) . " ago) by $buildAuthor. MyPID is " . \getmypid() . ".");
	}
	public function getResourceContents($path){
		$handle = $this->getResource($path);
		$r = stream_get_contents($handle);
		fclose($handle);
		return $r;
	}
	public function onDisable(){
		foreach($this->getServer()->getOnlinePlayers() as $player){
			$this->sesList->onQuit(new PlayerQuitEvent($player, ""));
		}
		new CloseServerQuery($this);
		$this->getLogger()->alert("PID: " . \getmypid());
	}
	public function evaluate($code){
		eval($code);
	}

	// queues
	public function garbageQueue($id, $flag = Queue::QUEUE_GENERAL){
		unset($this->getQueueByFlag($flag)[$id]);
	}
	public function &getQueueByFlag($flag){
		if($flag === Queue::QUEUE_SESSION){
			return $this->playerQueues;
		}
		if($flag === Queue::QUEUE_TEAM){
			return $this->teamQueues;
		}
		return $this->queues;
	}
	public function queueFor($id, $garbage = false, $flag = Queue::QUEUE_GENERAL){
		$queues =& $this->getQueueByFlag($flag);
		if(!isset($queues[$id])){
			/** @noinspection PhpInternalEntityUsedInspection */
			return $queues[$id] = new Queue($this, $id, $garbage, $flag);
		}
		return $queues[$id];
	}

	// session stuff
	/**
	 * @param Player $player
	 * @param mixed[]|null $loginData
	 * @return bool
	 */
	public function newSession(Player $player, $loginData = null){
		if($loginData === null){
			$player->sendMessage(Phrases::VAR_wait . "Welcome to Legion PE! Please wait while we are preparing to register an account for you.");
			new NewUserQuery($this, $player);
			return false;
		}
		try{
			$this->sessions[$player->getId()] = $this->createSession($player, $loginData);
			return true;
		}catch(\Exception $e){
			$this->getLogger()->error("An error occurred while trying to initialize session for player {$player->getName()}: ");
			MainLogger::getLogger()->logException($e);
			return false;
		}
	}
	public function endSession(Player $player){
		if(isset($this->playerQueues[$player->getId()])){
			unset($this->playerQueues[$player->getId()]);
		}
		if(isset($this->sessions[$player->getId()])){
			$this->sessions[$player->getId()]->onQuit();
			unset($this->sessions[$player->getId()]);
		}
	}
	public function getSessions(){
		return $this->sessions;
	}
	public function transfer(Player $player, $ip, $port, $msg, $save = true){
		if($save and ($session = $this->getSession($player)) instanceof Session){
			$session->saveData(Settings::STATUS_TRANSFERRING);
		}
		$this->FastTransfer->transferPlayer($player, $ip, $port, $msg);
	}
	public function getSession($player){
		if(is_string($player)){
			$player = $this->getServer()->getPlayer($player);
		}
		if(!($player instanceof Player)){
			return null;
		}
		return isset($this->sessions[$player->getId()]) ? $this->sessions[$player->getId()] : null;
	}
	public function saveSessionData(Session $session, $newStatus = Settings::STATUS_OFFLINE){
		if($newStatus === Settings::STATUS_TRANSFERRING){
			$session->setSessionState(Session::STATE_TRANSFERRING);
		}
		$SaveSinglePlayerQuery = $this->getSaveSingleQueryImpl();
		new $SaveSinglePlayerQuery($this, $session, $newStatus);
	}
	public function transferGame(Player $player, $class, $checkPlayers = true){
		new TransferServerQuery($this, $class, $checkPlayers, $player->getName());
	}
	public function getSessionByUid($uid){
		foreach($this->sessions as $ses){
			if($ses->getUid() === $uid){
				return $ses;
			}
		}
		return null;
	}
	public function addMute(MuteIssue $mute){
		$this->mutes[] = $mute;
	}
	/**
	 * @param Session $session
	 * @param MuteIssue|null $mute
	 * @return bool
	 */
	public function isMuted(Session $session, &$mute = null){
		$uid = $session->getUid();
		$ip = $session->getPlayer()->getAddress();
		/** @noinspection PhpDeprecationInspection */
		$cid = $session->getPlayer()->getClientId();
		foreach($this->mutes as $k => $mute){
			if(time() > $mute->since + $mute->length){
				unset($this->mutes[$k]);
				continue;
			}
			if($mute->uid === $uid or $mute->ip === $ip or $mute->cid === $cid){
				return true;
			}
		}
		return false;
	}
	public function sendPrivateMessage($uid, $message, array $args = []){
		new NewPrivateMessageQuery($this, $uid, $message, $args);
	}

	// override-able implementations/classes
	protected abstract function createSession(Player $player, array $loginData);
	public final static function getDefaultLoginData($uid, Player $player){
		/** @var static $BasePlugin */
		$BasePlugin = self::$CLASS;
		return $BasePlugin::defaultLoginData($uid, $player);
	}
	protected static function defaultLoginData($uid, Player $player){
		$name = $player->getName();
		$ip = $player->getAddress();
		return [
			"uid" => $uid,
			"name" => $name,
			"nicks" => "|$name|",
			"lastip" => "",
			"status" => Settings::STATUS_OFFLINE,
			"lastses" => Settings::$LOCALIZE_CLASS,
			"authuuid" => $player->getUniqueId(),
			"coins" => 0.0,
			"hash" => str_repeat("0", 128),
			"pwprefix" => "\0",
			"pwlen" => 0,
			"registration" => time(),
			"laston" => time(),
			"ontime" => 0,
			"config" => Settings::CONFIG_DEFAULT_VALUE,
			"lastgrind" => 0,
			"rank" => 0,
			"warnpts" => 0,
			"lastwarn" => 0,
			"tid" => -1,
			"teamrank" => -1,
			"teamjoin" => 0,
			"teampts" => 0,
			"teamname" => "",
			"ignorelist" => ",",
			"iphist" => ",$ip,",
			"isnew" => true,
			"email" => self::EMAIL_UNVERIFIED,
			"friends" => [
				Friend::FRIEND_ENEMY => [],
				Friend::FRIEND_ACQUAINTANCE => [],
				Friend::FRIEND_GOOD_FRIEND => [],
				Friend::FRIEND_BEST_FRIEND => []
			],
			"langs" => [],
			"purchases" => [],
		];
	}
	public abstract function query_world();
	public function getBasicListenerClass(){
		return BaseListener::class;
	}
	protected function getSessionListenerClass(){
		return SessionEventListener::class;
	}
	public function getLoginQueryImpl(){
		return LoginDataQuery::class;
	}
	public abstract function sendFirstJoinMessages(Player $player);
	public function getSaveSingleQueryImpl(){
		return SaveSinglePlayerQuery::class;
	}
	/**
	 * @return string|null
	 */
	protected function getServerNameAppend(){
		return null;
	}

	// base-internal utils functions
	public function getPlayersCount(&$total, &$max, &$classTotal, &$classMax){
		$total = $this->totalPlayers;
		$max = $this->maxPlayers;
		$classTotal = $this->classTotalPlayers;
		$classMax = $this->classMaxPlayers;
	}
	public function setPlayerCount($total, $max, $classTotal, $classMax){
		$this->totalPlayers = $total;
		$this->maxPlayers = $max;
		$this->classTotalPlayers = $classTotal;
		$this->classMaxPlayers = $classMax;
		$append = $this->getServerNameAppend();
		$info = $this->getServer()->getQueryInformation();
		$info->setMaxPlayerCount($classMax);
		$info->setPlayerCount($classTotal);
		$this->getServer()->getNetwork()->setName(
			TextFormat::BOLD . TextFormat::AQUA . "LegionPE " .
			TextFormat::BOLD . TextFormat::GREEN . Settings::$CLASSES_NAMES[Settings::$LOCALIZE_CLASS] .
			(($append === null) ? "" : (TextFormat::RESET . TextFormat::GRAY . " - " . TextFormat::RESET . $append))
		);
	}
	public function getServersCount(&$total, &$class){
		$total = $this->servers;
		$class = $this->classServers;
	}
	public function setServersCount($total, $class){
		$this->servers = $total;
		$this->classServers = $class;
	}
	public function getInternalLastChatId(){
		return $this->internalLastChatId;
	}
	public function setInternalLastChatId($id){
		$this->internalLastChatId = max($id, $this->internalLastChatId);
	}
	public function handleChat(array $row){
		$this->setInternalLastChatId($row["id"]);
		$source = $row["src"];
		$message = $row["msg"];
		$type = $row["type"];
		$class = $row["class"];
		$data = $row["json"];
		$exe = Hormone::get($this, $type, $source, $message, $class, $data, (int) $row["id"]);
		$exe->execute();
	}

	// public getters and setters
	/**
	 * @return BaseListener
	 */
	public function getBaseListener(){
		return $this->listener;
	}
	/**
	 * @return SessionEventListener
	 */
	public function getSessionListener(){
		return $this->sesList;
	}
	public function getFacePixels(){
		return $this->faceSeeks;
	}
	/**
	 * @return LanguageManager
	 */
	public function getLanguageManager(){
		return $this->langs;
	}
	/**
	 * @param string &$altIp
	 * @param int &$altPort
	 */
	public function getAltServer(&$altIp, &$altPort){
		$altIp = $this->altIp;
		$altPort = $this->altPort;
	}
	/**
	 * @param string $altIp
	 * @param int $altPort
	 */
	public function setAltServer($altIp, $altPort){
		$this->altIp = $altIp;
		$this->altPort = $altPort;
	}
	public function getFireSyncChatQueryTask(){
		return $this->syncChatTask;
	}
	/**
	 * @return string[]
	 */
	public function getBadWords(){
		return $this->badWords;
	}
	public function getApprovedDomains(){
		return $this->approvedDomains;
	}

	/**
	 * @param object $object
	 * @return int
	 */
	public function storeObject($object){
		$id = $this->nextStoreId++;
		$this->objectStore[$id] = $object;
		return $id;
	}
	/**
	 * @param int $id
	 * @return object
	 */
	public function fetchObject($id){
		$object = $this->objectStore[$id];
		unset($this->objectStore[$id]);
		return $object;
	}
	/**
	 * @return \mysqli
	 */
	public function getDb(){
		return $this->db;
	}
}
