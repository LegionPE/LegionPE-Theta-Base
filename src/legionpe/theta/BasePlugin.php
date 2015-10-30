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
use legionpe\theta\utils\CallbackPluginTask;
use legionpe\theta\utils\DbPingQuery;
use legionpe\theta\utils\FireSyncChatQueryTask;
use legionpe\theta\utils\MUtils;
use legionpe\theta\utils\OldLoginPacket;
use legionpe\theta\utils\RandomBroadcastTask;
use legionpe\theta\utils\ResendPlayersTask;
use legionpe\theta\utils\RestartServerTask;
use legionpe\theta\utils\SessionTickTask;
use legionpe\theta\utils\SyncStatusTask;
use legionpe\theta\utils\TransferPacket;
use libtheta\info\pvp\PvpKitInfo;
use pocketmine\command\defaults\TimingsCommand;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\TimingsHandler;
use pocketmine\network\RakLibInterface;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\MainLogger;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;

abstract class BasePlugin extends PluginBase{
	const EMAIL_UNVERIFIED = "NOTSET";
	private static $NAME = null;
	private static $CLASS = null;
	/** @var SessionEventListener */
	protected $sesList;
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
	/** @var resource */
	public $pmLog;
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
	/** @var int */
	private $restartTime;
	/** @var int */
	public $newJoins = 0;

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
		class_exists(CloseServerQuery::class); // preload to workaround frequent corruption errors due to phar replaced
		class_exists(SaveSinglePlayerQuery::class);
		class_exists(LoginDataQuery::class);
		if(!is_dir($this->getDataFolder())){
			mkdir($this->getDataFolder());
		}
		PvpKitInfo::init();
	}
	public function onEnable(){
		$this->langs = new LanguageManager($this);
		ThetaCommand::registerAll($this, $this->getServer()->getCommandMap());
		$this->getServer()->getNetwork()->registerPacket(OldLoginPacket::NETWORK_ID, OldLoginPacket::class);
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
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new RestartServerTask($this), 6000);
		$this->restartTime = $this->getServer()->getTick() + 72000;
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new RandomBroadcastTask($this), 2400, 2400);
		$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackPluginTask($this, function (){
			$plugin = $this->getServer()->getPluginManager()->getPlugin("FastTransfer");
			if($plugin instanceof Plugin and $plugin->isEnabled()){
				$this->getServer()->getPluginManager()->disablePlugin($plugin);
			}
		}), 2);

		$RESEND_ADD_PLAYER = $this->getResendAddPlayerFreq();
		if($RESEND_ADD_PLAYER > 0){
			$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new ResendPlayersTask($this), $RESEND_ADD_PLAYER, $RESEND_ADD_PLAYER);
		}
		/** @noinspection PhpUsageOfSilenceOperatorInspection */
		@touch($this->getDataFolder() . "privmsg.log");
		$this->pmLog = fopen($this->getDataFolder() . "privmsg.log", "at");
		$this->faceSeeks = json_decode($this->getResourceContents("head.json"));
		$this->badWords = json_decode($this->getResourceContents("words.json"));
		$this->approvedDomains = json_decode($this->getResourceContents("approvedDomains.json"));
		$buildInfo = json_decode($this->getResourceContents("build.json"));
		$compileTime = $buildInfo->time;
		$buildNumber = $buildInfo->buildNumber;
		$buildAuthor = $buildInfo->buildAuthor;
		Utils::getURL(Credentials::IRC_WEBHOOK_STATUS . urlencode("[Status] [" . Settings::$CLASSES_NAMES[Settings::$LOCALIZE_CLASS] . "] Server at " . Settings::$LOCALIZE_IP . ":" . Settings::$LOCALIZE_PORT . " started."), 3);
		$this->getServer()->getPluginManager()->setUseTimings(true);
		TimingsHandler::reload();
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
		/** @noinspection PhpUsageOfSilenceOperatorInspection */
		@fclose($this->pmLog);
		$url = $this->pasteTimings();
		Utils::getURL(Credentials::IRC_WEBHOOK_STATUS . urlencode("[Status] [" . Settings::$CLASSES_NAMES[Settings::$LOCALIZE_CLASS] . "] Server at " . Settings::$LOCALIZE_IP . ":" . Settings::$LOCALIZE_PORT . " (PID " . \getmypid() . ") stopped. Timings: $url"), 3);
		$this->getLogger()->alert("PID: " . \getmypid());
	}
	private function pasteTimings(){
		$sampleTime = microtime(true) - TimingsCommand::$timingStart;
		$stream = fopen("php://temp", "r+b");
		TimingsHandler::printTimings($stream);
		fwrite($stream, "Sample time " . round($sampleTime * 1000000000) . " (" . $sampleTime . "s)" . PHP_EOL);
		fseek($stream, 0);
		$data = ["syntax" => "text", "poster" => "LegionPE", "content" => stream_get_contents($stream)];
		$ch = curl_init("http://paste.ubuntu.com/");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_AUTOREFERER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["User-Agent: LegionPE " . $this->getDescription()->getVersion()]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		curl_close($ch);
		if(preg_match('#^Location: http://paste\\.ubuntu\\.com/([0-9]{1,})/#m', $data, $matches) == 0){
			return "about:blank";
		}
		fclose($stream);
		return "http://timings.aikar.co/?url=$matches[1]";
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
		if(!empty($msg)){
			$player->sendMessage($msg);
		}
		$this->transferPlayer0($player, $ip, $port);
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
			"authuuid" => $player->getClientSecret(),
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
			"channels" => [],
			"kitrowsarray" => []
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
	protected function getResendAddPlayerFreq(){
		return 60;
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
		$this->getBaseListener()->onQueryRegen($info);
		$this->getServer()->getNetwork()->setName(
			TextFormat::BOLD . TextFormat::AQUA . "LegionPE " .
			TextFormat::BOLD . TextFormat::GREEN . ((Settings::$LOCALIZE_CLASS === Settings::CLASS_HUB) ? "Theta" : Settings::$CLASSES_NAMES[Settings::$LOCALIZE_CLASS]) .
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
	/**
	 * @return int
	 */
	public function getRestartTime(){
		return $this->restartTime;
	}
	private function transferPlayer0(Player $player, $ip, $port){
		$sp = new TransferPacket;
		$sp->address = $this->getHostByName($ip);
		$sp->port = $port;
		$player->dataPacket($sp);
		$interfaces = $this->getServer()->getNetwork()->getInterfaces();
		foreach($interfaces as $interface){
			if($interface instanceof RakLibInterface){
//				$class = new \ReflectionClass(RakLibInterface::class);
//				$prop = $class->getProperty("rakLib");
//				$prop->setAccessible(true);
//				/** @var RakLibServer $value */
//				$value = $prop->getValue($interface);
				$identifier = $player->getAddress() . ":" . $player->getPort();
//				$value->pushMainToThreadPacket(RakLib::PACKET_CLOSE_SESSION . chr(strlen($identifier)) . $identifier);
				$interface->closeSession($identifier, "transferring");
			}
		}
	}
	private $hosts = [];
	public function getHostByName($host){
		if(isset($this->hosts[$host])){
			return $this->hosts[$host];
		}
		return $this->hosts[$host] = gethostbyname($host);
	}
}
