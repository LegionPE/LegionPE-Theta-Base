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

namespace legionpe\theta;

use legionpe\theta\chat\ChatType;
use legionpe\theta\command\ThetaCommand;
use legionpe\theta\config\Settings;
use legionpe\theta\lang\LanguageManager;
use legionpe\theta\lang\Phrases;
use legionpe\theta\query\CloseServerQuery;
use legionpe\theta\query\InitDbQuery;
use legionpe\theta\query\LoginDataQuery;
use legionpe\theta\query\NewUserQuery;
use legionpe\theta\query\SaveSinglePlayerQuery;
use legionpe\theta\query\SearchServerQuery;
use legionpe\theta\queue\Queue;
use legionpe\theta\queue\TransferSearchRunnable;
use legionpe\theta\utils\FireSyncChatQueryTask;
use legionpe\theta\utils\SessionTickTask;
use legionpe\theta\utils\SyncStatusTask;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use shoghicp\FastTransfer\FastTransfer;

abstract class BasePlugin extends PluginBase{
	const EMAIL_UNVERIFIED = "~NOTSET";
	private static $NAME = null;
	/** @var SessionEventListener */
	protected $sesList;
	/** @var FastTransfer */
	private $FastTransfer;
	/** @var BaseListener */
	private $listener;
	/** @var LanguageManager */
	private $langs;
	/** @var int[] */
	private $faceSeeks = [];
	/** @var Queue[] */
	private $queues = [], $playerQueues = [], $teamQueues = [];
	/** @var Session[] */
	private $sessions = [];
	private $totalPlayers, $maxPlayers, $classTotalPlayers, $classMaxPlayers;
	/** @var string */
	private $altIp;
	/** @var int */
	private $altPort;
	/** @var int */
	private $internalLastChatId = null;
	/** @var FireSyncChatQueryTask */
	private $syncChatTask;

	// PluginManager-level stuff
	/**
	 * @param Server $server
	 * @return BasePlugin
	 * @deprecated
	 */
	public static function getInstance(Server $server){
		return $server->getPluginManager()->getPlugin(self::$NAME);
	}
	public final static function getDefaultLoginData($uid, Player $player){
		return static::defaultLoginData($uid, $player);
	}
	protected static function defaultLoginData($uid, Player $player){
		$name = $player->getName();
		$ip = $player->getAddress();
		return [
			"uid" => $uid,
			"name" => $name,
			"nicks" => "|$name|",
			"lastip" => "",
			"status" => Settings::STATUS_ONLINE,
			"lastses" => Settings::$LOCALIZE_CLASS,
			"authuuid" => $player->getUniqueId(),
			"coins" => 100.0,
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
			"ignorelist" => ",",
			"iphist" => ",$ip,",
			"isnew" => true,
			"email" => self::EMAIL_UNVERIFIED,
			"friends" => [],
			"langs" => [],
			"purchases" => [],
		];
	}
	public function onLoad(){
		self::$NAME = $this->getName();
		class_exists(CloseServerQuery::class); // preload to workaround frequent corruption errors due to phar repalced
		if(!is_dir($this->getDataFolder())){
			mkdir($this->getDataFolder());
		}
	}
	public function onEnable(){
		ThetaCommand::registerAll($this, $this->getServer()->getCommandMap());
		$this->FastTransfer = $this->getServer()->getPluginManager()->getPlugin("FastTransfer");
		$class = $this->getBasicListener();
		$this->getServer()->getPluginManager()->registerEvents($this->listener = new $class($this), $this);
		$class = $this->getSessionListenerClass();
		$this->getServer()->getPluginManager()->registerEvents($this->sesList = new $class($this), $this);
		new InitDbQuery($this);
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new SyncStatusTask($this), 40, 40);
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new SessionTickTask($this), 1, 20);
		$this->getServer()->getScheduler()->scheduleDelayedTask($this->syncChatTask = new FireSyncChatQueryTask($this), 5);
		$this->faceSeeks = json_decode($this->getResourceContents("head.json"));
		$this->langs = new LanguageManager($this);
	}
	public function getBasicListener(){
		return BaseListener::class;
	}
	protected function getSessionListenerClass(){
		return SessionEventListener::class;
	}

	// queues
	public function getResourceContents($path){
		$handle = $this->getResource($path);
		$r = stream_get_contents($handle);
		fclose($handle);
		return $r;
	}
	public function onDisable(){
		foreach($this->getServer()->getOnlinePlayers() as $player){
			$player->kick("Server stop", false);
		}
		new CloseServerQuery($this);
	}
	public function evaluate($code){
		eval($code);
	}

	// session stuff
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
			return false;
		}
	}
	protected abstract function createSession(Player $player, array $loginData);
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
	public function getLoginQueryImpl(){
		return LoginDataQuery::class;
	}

	// override-able implementations/classes
	public function getSaveSingleQueryImpl(){
		return SaveSinglePlayerQuery::class;
	}
	public abstract function sendFirstJoinMessages(Player $player);
	public abstract function query_world();
	public function handleChat(array $row){
		$this->setInternalLastChatId($row["id"]);
		$source = $row["src"];
		$message = $row["msg"];
		$type = $row["type"];
		$class = $row["class"];
		$data = $row["json"];
		$exe = ChatType::get($this, $type, $source, $message, $class, $data);
		$exe->execute();
	}
	public function transfer(Player $player, $ip, $port, $msg, $save = true){
		if($save and ($session = $this->getSession($player)) instanceof Session){
			$this->saveSessionData($session, Settings::STATUS_TRANSFERRING);
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
		new SaveSinglePlayerQuery($this, $session, $newStatus);
	}
	public function getSessionByUid($uid){
		foreach($this->sessions as $ses){
			if($ses->getUid() === $uid){
				return $ses;
			}
		}
		return null;
	}
	public function transferGame(Player $player, $class, $checkPlayers = true){
		$task = new SearchServerQuery($this, $class, $checkPlayers);
		$this->queueFor($player->getId(), true, Queue::QUEUE_SESSION)
			->pushToQueue(new TransferSearchRunnable($this, $player, $task));
	}

	// global-level utils functions
	public function queueFor($id, $garbage = false, $flag = Queue::QUEUE_GENERAL){
		$queues =& $this->getQueueByFlag($flag);
		if(!isset($queues[$id])){
			/** @noinspection PhpInternalEntityUsedInspection */
			return $queues[$id] = new Queue($this, $id, $garbage, $flag);
		}
		return $queues[$id];
	}
	public function setPlayerCount($total, $max, $classTotal, $classMax){
		$this->totalPlayers = $total;
		$this->maxPlayers = $max;
		$this->classTotalPlayers = $classTotal;
		$this->classMaxPlayers = $classMax;
		$append = $this->getServerNameAppend();
		$online = count($this->getServer()->getOnlinePlayers());
		$this->getServer()->getNetwork()->setName(
			TextFormat::BOLD . TextFormat::AQUA . "LegionPE " .
			TextFormat::BOLD . TextFormat::GREEN . Settings::$CLASSES_NAMES[Settings::$LOCALIZE_CLASS] .
			TextFormat::RESET . TextFormat::DARK_AQUA . " [$online/$classTotal/$total/$max]" .
			(($append === null) ? "" : (TextFormat::RESET . TextFormat::GRAY . " - " . TextFormat::RESET . $append))
		);
	}
	/**
	 * @return string|null
	 */
	protected function getServerNameAppend(){
		return null;
	}
	public function getPlayersCount(&$total, &$max, &$classTotal, &$classMax){
		$total = $this->totalPlayers;
		$max = $this->maxPlayers;
		$classTotal = $this->classTotalPlayers;
		$classMax = $this->classMaxPlayers;
	}

	// public getters and setters
	/**
	 * @return BaseListener
	 */
	public function getListener(){
		return $this->listener;
	}
	/**
	 * @return SessionEventListener
	 */
	public function getSesList(){
		return $this->sesList;
	}
	public function getFaceSeeks(){
		return $this->faceSeeks;
	}
	public function getLangs(){
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
	public function getInternalLastChatId(){
		return $this->internalLastChatId;
	}
	public function setInternalLastChatId($id){
		$this->internalLastChatId = max($id, $this->internalLastChatId);
	}
	public function getFireSyncChatQueryTask(){
		return $this->syncChatTask;
	}
}
