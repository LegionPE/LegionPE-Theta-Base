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

use legionpe\theta\chat\ChannelChatType;
use legionpe\theta\chat\TeamChatType;
use legionpe\theta\config\Settings;
use legionpe\theta\lang\Phrases;
use legionpe\theta\query\AddIpQuery;
use legionpe\theta\query\NextIdQuery;
use legionpe\theta\queue\ExecuteWarningRunnable;
use legionpe\theta\queue\Queue;
use legionpe\theta\utils\MUtils;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\TextContainer;
use pocketmine\level\sound\FizzSound;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

abstract class Session{
	const AUTH_TRANSFER = 0;
	const AUTH_UUID = 1;
	const AUTH_IP_LAST = 2;
	const AUTH_IP_HIST = 3;
	const AUTH_SUBNET_LAST = 4;
	/** @deprecated */
	const AUTH_SUBNET_HIST = 5;
	const AUTH_PASS = 6;
	const AUTH_REG = 7;
	const STATE_LOADING = 0x00;
	const STATE_REGISTERING = 0x10;
	const STATE_REGISTERING_FIRST = self::STATE_REGISTERING;
	const STATE_REGISTERING_SECOND = self::STATE_REGISTERING | 0x01;
	const STATE_LOGIN = 0x20;
	const STATE_UPDATE_HASH = 0x30;
	const STATE_PLAYING = 0x40;
	const CHAT_STD = 0;
	const CHAT_ME = 1;
	const CHAT_LOCAL = 2;
	const CHANNEL_LOCAL = "&local";
	const CHANNEL_TEAM = "&team";
	public static $AUTH_METHODS = [
		self::AUTH_TRANSFER => "transferring",
		self::AUTH_UUID => "matching unique ID",
		self::AUTH_IP_LAST => "matching last IP",
		self::AUTH_IP_HIST => "matching IP history",
		self::AUTH_PASS => "matching password",
		self::AUTH_REG => "registering"
	];
	public static $AUTH_METHODS_PHRASES = [
		self::AUTH_TRANSFER => "login.auth.method.transfer",
		self::AUTH_UUID => "login.auth.method.uuid",
		self::AUTH_IP_LAST => "login.auth.method.ip.last",
		self::AUTH_IP_HIST => "login.auth.method.ip.hist",
		self::AUTH_PASS => "login.auth.method.pass",
		self::AUTH_REG => "login.auth.method.register"
	];
	/** @var Player */
	private $player;
	/** @var mixed[] */
	private $loginData;
	/** @var int */
	private $joinTime;
	/** @var float */
	private $coinsOld = 0;
	/** @var float */
	private $ontimeSince;
	/** @var int */
	private $state = self::STATE_LOADING;
	private $invisibleFrom = [];
	public $confirmGrind = false;
	public $currentChatState = self::CHANNEL_LOCAL;
	/** @var string|TextContainer|null */
	private $tmpHash = null, $curPopup = null;
	public function __construct(Player $player, $loginData){
		$this->player = $player;
		$this->loginData = $loginData;
		$this->joinTime = time();
		$this->coinsOld = $loginData["coins"];
		$this->ontimeSince = microtime(true);
		if($this->init() === false){
			throw new \Exception;
		}
	}
	public function __toString(){
		return $this->getPlayer()->getDisplayName();
	}
	public function onJoin(){
		foreach($this->player->getLevel()->getChunkPlayers($this->player->getFloorX() >> 4, $this->player->getFloorZ() >> 4) as $other){
			$other->hidePlayer($this->player);
			$this->invisibleFrom[$other->getId()] = true;
		}
		$this->prepareLogin();
	}
	public function onCmd(PlayerCommandPreprocessEvent $event){
		if($this->isRegistering()){
			$event->setCancelled();
			$len = strlen($event->getMessage());
			$one = substr($event->getMessage(), 0, 1);
			$event->setMessage($hash = self::hash($event->getMessage(), $this->getUid()));
			if($this->state === self::STATE_REGISTERING_FIRST){
				$this->tmpHash = $hash;
				$this->sendCurlyLines();
				$this->send(Phrases::LOGIN_REGISTER_RETYPE);
				$this->state = self::STATE_REGISTERING_SECOND;
			}elseif($this->state === self::STATE_REGISTERING_SECOND){
				$this->sendCurlyLines();
				if($this->tmpHash === $hash){
					$this->sendCurlyLines();
					$this->setLoginDatum("hash", $hash);
					$this->setLoginDatum("pwprefix", $one);
					$this->setLoginDatum("pwlen", $len);
					$this->state = self::STATE_PLAYING;
					$this->sendFirstJoinMessages();
					$this->login(self::AUTH_REG);
					$this->send(Phrases::LOGIN_REGISTER_SUCCESS);
				}else{
					$this->send(Phrases::LOGIN_REGISTER_MISMATCH);
					$this->tmpHash = null;
					$this->state = self::STATE_REGISTERING_FIRST;
				}
			}
			return false;
		}elseif($this->isLoggingIn()){
			$event->setMessage($hash = self::hash($event->getMessage(), $this->getUid()));
			$this->sendCurlyLines();
			if($hash === $this->getPasswordHash()){
				$this->login(self::AUTH_PASS);
			}else{
				$this->state++;
				$chances = "chance";
				MUtils::word_quantitize($chances, 5 - $this->getStatePrecise());
				$this->send(Phrases::LOGIN_PASS_MISMATCH, ["chances" => $chances]);
				if($this->getStatePrecise() === 5){
					$this->getPlayer()->kick("Failure to login within 5 attempts");
					return false;
				}
			}
			return false;
		}else{
			$msg = $event->getMessage();
			$firstChar = $this->getLoginDatum("pwprefix");
			$len = $this->getLoginDatum("pwlen");
			$msgLen = strlen($msg);
			for($offset = 0; ($offset + $len <= $msgLen) and ($pos = strpos($msg, $firstChar, $offset)) !== false; $offset = $pos + 1){
				$sub = substr($msg, $pos, $len);
				$hash = $this->hash($sub, $this->getUid());
				if($hash === $this->getPasswordHash()){
					$this->send(Phrases::CHAT_BLOCKED_PASS);
					return false;
				}
			}
			$isLocal = substr($event->getMessage(), 0, 1) !== ".";
			if($isLocal){
				$event->setMessage(substr($event->getMessage(), 1));
			}
			if($this->currentChatState === self::CHANNEL_TEAM){
				$data = ["tid" => $this->getTeamId(), "teamName" => $this->getTeamName()];
				$type = new TeamChatType($this->getMain(), $this->getPlayer()->getDisplayName(), trim($event->getMessage()), $isLocal ? Settings::$LOCALIZE_CLASS : Settings::CLASS_ALL, $data);
				$type->push();
				return false;
			}

			if($this->currentChatState !== self::CHANNEL_LOCAL){
				$data = ["channel" => $this->currentChatState];
				$type = new ChannelChatType($this->getMain(), $this->getPlayer()->getDisplayName(), trim($event->getMessage()), $isLocal ? Settings::$LOCALIZE_CLASS : Settings::CLASS_ALL, $data);
				$type->push();
				return false;
			}
			$this->onChat(trim($event->getMessage()), $isLocal ? self::CHAT_LOCAL : self::CHAT_STD);
		}
		return true;
	}
	public function onDamage(/** @noinspection PhpUnusedParameterInspection */ EntityDamageEvent $event){
		if(!$this->isPlaying()){
			return false;
		}
		return true;
	}
	public function onMove(/** @noinspection PhpUnusedParameterInspection */ PlayerMoveEvent $event){
		if(!$this->isPlaying()){
			$this->setMaintainedPopup(TextFormat::RED . "Please " . ($this->isRegistering() ? "register" : "login") . " by typing your password directly into chat.");
			$from = $event->getFrom();
			$to = $event->getTo();
			return ($from->x === $to->x) and ($from->y === $to->y) and ($from->z === $to->z);
		}
		return true;
	}
	public function onConsume(/** @noinspection PhpUnusedParameterInspection */ PlayerItemConsumeEvent $event){
		if(!$this->isPlaying()){
			$this->setMaintainedPopup(TextFormat::RED . "Please " . ($this->isRegistering() ? "register" : "login") . " by typing your password directly into chat.");
			return false;
		}
		return true;
	}
	public function onDropItem(/** @noinspection PhpUnusedParameterInspection */ PlayerDropItemEvent $event){
		if(!$this->isPlaying()){
			$this->setMaintainedPopup(TextFormat::RED . "Please " . ($this->isRegistering() ? "register" : "login") . " by typing your password directly into chat.");
			return false;
		}
		return true;
	}
	public function onInteract(/** @noinspection PhpUnusedParameterInspection */ PlayerInteractEvent $event){
		if(!$this->isPlaying()){
			$this->setMaintainedPopup(TextFormat::RED . "Please " . ($this->isRegistering() ? "register" : "login") . " by typing your password directly into chat.");
			return false;
		}
		return true;
	}
	public function onRespawn(/** @noinspection PhpUnusedParameterInspection */ PlayerRespawnEvent $event){
		if(!$this->isPlaying()){
			$this->setMaintainedPopup(TextFormat::RED . "Please " . ($this->isRegistering() ? "register" : "login") . " by typing your password directly into chat.");
			return false;
		}
		return true;
	}
	public function onBreak(/** @noinspection PhpUnusedParameterInspection */ BlockBreakEvent $event){
		if(!$this->isPlaying()){
			$this->setMaintainedPopup(TextFormat::RED . "Please " . ($this->isRegistering() ? "register" : "login") . " by typing your password directly into chat.");
			return false;
		}
		return true;
	}
	public function onPlace(/** @noinspection PhpUnusedParameterInspection */ BlockPlaceEvent $event){
		if(!$this->isPlaying()){
			$this->setMaintainedPopup(TextFormat::RED . "Please " . ($this->isRegistering() ? "register" : "login") . " by typing your password directly into chat.");
			return false;
		}
		return true;
	}
	public function onOpenInv(/** @noinspection PhpUnusedParameterInspection */ InventoryOpenEvent $event){
		if(!$this->isPlaying()){
			$this->setMaintainedPopup(TextFormat::RED . "Please " . ($this->isRegistering() ? "register" : "login") . " by typing your password directly into chat.");
			return false;
		}
		return true;
	}
	public function onPickupItem(/** @noinspection PhpUnusedParameterInspection */ InventoryPickupItemEvent $event){
		if(!$this->isPlaying()){
			return false;
		}
		return true;
	}
	public function onPickupArrow(/** @noinspection PhpUnusedParameterInspection */ InventoryPickupArrowEvent $event){
		if(!$this->isPlaying()){
			return false;
		}
		return true;
	}
	public function onChatEvent(/** @noinspection PhpUnusedParameterInspection */ PlayerChatEvent $event){
		$msg = $event->getMessage();
		$this->onChat($msg, self::CHAT_STD);
		return false;
	}
	public function onHoldItem(/** @noinspection PhpUnusedParameterInspection */ PlayerItemHeldEvent $event){
		return true;
	}
	public function onTeleport(/** @noinspection PhpUnusedParameterInspection */ EntityTeleportEvent $event){
		return true;
	}
	public function onQuit(){
		$this->saveData();
	}

	/**
	 * @return Player
	 */
	public function getPlayer(){
		return $this->player;
	}
	public function getLoginDatum($key){
		return isset($this->loginData[$key]) ? $this->loginData[$key] : null;
	}
	public function setLoginDatum($key, $datum){
		$this->loginData[$key] = $datum;
	}
	public function incrLoginDatum($key, $amplitude = 1){
		$this->loginData[$key] += $amplitude;
	}
	public function getUid(){
		return $this->getLoginDatum("uid");
	}
	public function getNicks(){
		return array_filter(explode("|", $this->getLoginDatum("nicks")));
	}
	public function getIPHistory(){
		return array_filter(explode(",", $this->getLoginDatum("iphist")));
	}
	public function addIp($ip){
		$this->setLoginDatum("iphist", $this->getLoginDatum("iphist") . "$ip,");
		new AddIpQuery($this->getMain(), $ip, $this->getUid());
	}
	public function getCoins(){
		return $this->getLoginDatum("coins");
	}
	public function setCoins($coins){
		$this->setLoginDatum("coins", $coins);
	}
	public function grantCoins($coins, $ignoreGrind = false, $sound = true){
		if(!$ignoreGrind and $this->isGrinding()){
			$coins *= Settings::getGrindFactor($this->getRank());
		}
		if($sound){
			$this->getPlayer()->getLevel()->addSound(new FizzSound($this->getPlayer()), [$this->getPlayer()]);
		}
		$this->setCoins($this->getCoins() + $coins);
	}
	public function getAndUpdateCoinsDelta(){
		$coins = $this->getCoins();
		$delta = $coins - $this->coinsOld;
		$this->coinsOld = $coins;
		return $delta;
	}
	public function getAndUpdateOntime(){
		$now = microtime(true);
		$result = $now - $this->ontimeSince;
		$this->ontimeSince = $now;
		return $result;
	}
	public function getPasswordHash(){
		return $this->getLoginDatum("hash");
	}
	public function getPasswordPrefix(){
		return $this->getLoginDatum("pwprefix");
	}
	public function getPasswordLength(){
		return $this->getLoginDatum("pwlen");
	}
	public function getRegisterTime(){
		return $this->getLoginDatum("registration");
	}
	public function getLastOnline(){
		return $this->getLoginDatum("laston");
	}
	public function getAllSettings(){
		return $this->getLoginDatum("config");
	}
	public function getAuthSettings(){
		return $this->getLoginDatum("config") & Settings::CONFIG_SECTOR_AUTH;
	}
	public function getTagEnabled(){
		return (bool) ($this->getLoginDatum("config") & Settings::CONFIG_TAG_ON);
	}
	public function getStatsPublic(){
		return (bool) ($this->getLoginDatum("config") & Settings::CONFIG_STATS_PUBLIC);
	}
	public function isLocalChatOn(){
		return (bool) ($this->getLoginDatum("config") & Settings::CONFIG_LOCAL_CHAT_ON);
	}
	public function isTeamChannelOn(){
		return (bool) ($this->getLoginDatum("config") & Settings::CONFIG_TEAM_CHANNEL_ON);
	}
	public function getLastGrind(){
		return $this->getLoginDatum("lastgrind");
	}
	public function isGrinding(){
		return time() - $this->getLastGrind() <= Settings::getGrindLength($this->getRank());
	}
	public function canStartGrind(){
		if(!$this->isDonator()){
			return false;
		}
		return time() - $this->getLastGrind() >= Settings::getGrindExpiry($this->getRank());
	}
	public function getGrindWaitTime(){
		return max(0, $this->getLastGrind() + Settings::getGrindExpiry($this->getRank()) - time());
	}
	public function startGrinding(){
		$this->setLoginDatum("lastgrind", time());
	}
	public function getRank(){
		return $this->getLoginDatum("rank");
	}
	public function isModerator($includeTrial = true){
		$rank = $this->getRank();
		return ($rank & Settings::RANK_PERM_MOD) and ($includeTrial or ($rank & Settings::RANK_PREC_TRIAL) === 0);
	}
	public function isAdmin($includeTrial = true){
		$rank = $this->getRank();
		return ($rank & Settings::RANK_PERM_ADMIN) and ($includeTrial or ($rank & Settings::RANK_PREC_TRIAL) === 0);
	}
	public function isDonator(){
		return (bool) ($this->getRank() & Settings::RANK_IMPORTANCE_DONATOR);
	}
	public function isDonatorPlus(){
		return (bool) ($this->getRank() & Settings::RANK_IMPORTANCE_DONATOR_PLUS);
	}
	public function isVIP(){
		return (bool) ($this->getRank() & Settings::RANK_IMPORTANCE_VIP);
	}
	public function isVIPPlus(){
		return (bool) ($this->getRank() & Settings::RANK_IMPORTANCE_VIP_PLUS);
	}
	public function getChatColor(){
		if($this->isAdmin()){
			return TextFormat::LIGHT_PURPLE . TextFormat::BOLD;
		}
		if($this->isModerator()){
			return TextFormat::LIGHT_PURPLE;
		}
		if($this->isVIP()){
			return TextFormat::WHITE . TextFormat::BOLD;
		}
		if($this->isDonator()){
			return TextFormat::WHITE;
		}
		return TextFormat::GRAY;
	}
	public function getWarningPoints(){
		return $this->getLoginDatum("warnpts");
	}
	public function getLastWarnTime(){
		return $this->getLoginDatum("lastwarn");
	}
	public function getEffectiveConseq(){
		return Settings::getWarnPtsConseq($this->getWarningPoints(), $this->getLastWarnTime());
	}
	public function addWarningPoints($pts){
		$this->setLoginDatum("warnpts", $this->getWarningPoints() + $pts);
		$this->setLoginDatum("lastwarn", time());
	}
	public function warn($id, $points, CommandSender $issuer, $msg){
		$wid = new NextIdQuery($this->getMain(), NextIdQuery::WARNING);
		/** @noinspection PhpDeprecationInspection */
		$clientId = $this->getPlayer()->getClientId();
		$this->getMain()->queueFor($this->getPlayer()->getId(), true, Queue::QUEUE_SESSION)
			->pushToQueue(new ExecuteWarningRunnable($this->getMain(), $wid, $this->getUid(), $clientId, $id, $points, $issuer, $msg));
	}
	public function getTeamId(){
		return $this->getLoginDatum("tid");
	}
	public function getTeamName(){
		return "TEAM NAME HERE TODO"; // TODO
	}
	public function getTeamRank(){
		return $this->getLoginDatum("teamrank");
	}
	public function getTeamJoinTime(){
		return $this->getLoginDatum("teamjoin");
	}
	public function getIgnoreList(){
		return array_filter(explode(",", $this->getLoginDatum("ignorelist")));
	}
	public function isIgnoring($name, &$pos = 0){
		return ($pos = strpos($name, "," . strtolower($name) . ",")) !== false;
	}
	public function ignore($name){
		if(!$this->isIgnoring($name)){
			$this->setLoginDatum("ignorelist", $this->getLoginDatum("ignorelist") . strtolower($name) . ",");
			return true;
		}
		return false;
	}
	public function unignore($name){
		if($this->isIgnoring($name, $pos)){
			$list = $this->getLoginDatum("ignorelist");
			$this->setLoginDatum("ignorelist", substr($list, $pos, strlen($name) + 1));
			return true;
		}
		return false;
	}
	public function isNew(){
		return isset($this->loginData["isnew"]) and $this->loginData["isnew"] === true;
	}
	/**
	 * @return BasePlugin
	 */
	public abstract function getMain();
	public function getStatePrecise(){
		return $this->state & 0x0F;
	}
	public function isLoggingIn(){
		return ($this->state & 0xF0) === self::STATE_LOGIN;
	}
	public function isRegistering(){
		return ($this->state & 0xF0) === self::STATE_REGISTERING;
	}
	public function isPlaying(){
		return ($this->state & 0xF0) === self::STATE_PLAYING;
	}
	public function setMaintainedPopup($popup = null){
		if($this->curPopup === $popup){
			return;
		}
		$this->curPopup = $popup;
		if($popup !== null){
			$this->getPlayer()->sendPopup($popup);
		}else{
			$this->getPlayer()->sendPopup(" ");
		}
	}
	public function getPopup(){
		return $this->curPopup;
	}
	public function getCurrentFaceSkin(){
		$seeks = $this->getMain()->getFaceSeeks();
		$output = "";
		$skin = $this->getPlayer()->getSkinData();
		foreach($seeks as $seek){
			$output .= substr($skin, $seek / 2, 4);
		}
		return $output;
	}

	/**
	 * Override this method to do initialization stuff
	 * @param int $method
	 */
	public function login($method){
		$this->state = self::STATE_PLAYING;
		$this->send(Phrases::LOGIN_AUTH_SUCCESS, ["method" => $this->translate(self::$AUTH_METHODS_PHRASES[$method])]);
		$this->send(Phrases::LOGIN_AUTH_WHEREAMI, ["class" => $this->translate(Settings::$CLASSES_NAMES_PHRASES[Settings::$LOCALIZE_CLASS]), "ip" => Settings::$LOCALIZE_IP, "port" => (string) Settings::$LOCALIZE_PORT]);
		$this->setMaintainedPopup();
	}
	public function send($phrase, array $vars = []){
		$this->getPlayer()->sendMessage($this->translate($phrase, $vars));
	}
	public function translate($phrase, array $vars = []){
		return $this->getMain()->getLangs()->get($phrase, $vars, "en"); // TODO custom language
	}
	public function sendCurlyLines($lines = 1, $color = TextFormat::ITALIC . TextFormat::RED){
		for($i = 0; $i < $lines; $i++){
			$this->getPlayer()->sendMessage($color . str_repeat("~", 40));
		}
	}
	public function recalculateNameTag(){
		$this->getPlayer()->setNameTag($this->calculateTag());
	}
	public function calculateTag($nameColor = TextFormat::WHITE){
		$rank = $this->calculateRank();
		if($rank !== ""){
			$tag = TextFormat::AQUA . "{" .  $rank . TextFormat::AQUA . "}";
		}else{
			$tag = "";
		}
		// TODO team tags
		// TODO custom tags
		$tag .= $nameColor . $this->getPlayer()->getName();
		return $tag;
	}

	protected function init(){
		$conseq = $this->getEffectiveConseq();
		if($conseq->banLength > 0){
			$left = MUtils::time_secsToString($conseq->banLength);
			$this->getPlayer()->kick(TextFormat::RED . "You are banned.\nYou have accumulated " . TextFormat::DARK_PURPLE . $this->getWarningPoints() . TextFormat::RED . " warning points,\nand you still have " . TextFormat::BLUE . $left . TextFormat::RED . " before you are unbanned.\n" . TextFormat::AQUA . "Believe this to be a mistake? Email us at " . TextFormat::DARK_PURPLE . "support@legionpvp.eu");
			return false;
		}
		return true;
	}
	private function prepareLogin(){
		$status = $this->getLoginDatum("status");
		if($status === Settings::STATUS_TRANSFERRING and $this->getPlayer()->getUniqueId() === $this->getLoginDatum("authuuid")){
			$this->login(self::AUTH_TRANSFER);
			return;
		}
		if($this->getLoginDatum("isnew")){
			$this->state = self::STATE_REGISTERING;
		}else{
			$method = $this->getAuthSettings();
			if(!$this->getLoginDatum("isnew") and $method === Settings::CONFIG_AUTH_UUID and $this->getPlayer()->getUniqueId() === $this->getLoginDatum("authuuid")){
				$this->login(self::AUTH_UUID);
				return;
			}
			if($method === Settings::CONFIG_AUTH_IP_LAST and $this->getPlayer()->getAddress() === $this->getLoginDatum("lastip")){
				$this->login(self::AUTH_IP_LAST);
				return;
			}
			if($method === Settings::CONFIG_AUTH_IP_HISTORY and in_array($this->getPlayer()->getAddress(), $this->getIPHistory())){
				$this->login(self::AUTH_IP_HIST);
				return;
			}
			if($method === Settings::CONFIG_AUTH_SUBNET_LAST and $this->subnet_matches($this->getPlayer()->getAddress(), $this->getLoginDatum("lastip"))){
				$this->login(self::AUTH_SUBNET_LAST);
			}
			// deprecated: subnet hist
			$this->state = self::STATE_LOGIN;
		}
		if($this->isLoggingIn()){
			$this->send(Phrases::LOGIN_PASS_PROMPT);
		}else{
			$this->send(Phrases::LOGIN_REGISTER_PROMPT);
		}
	}
	private function subnet_matches($ip0, $ip1){
		if($ip0 === $ip1){
			return true;
		}
		$ip0 = explode(".", $ip0);
		$ip1 = explode(".", $ip1);
		if(count($ip0) !== 4 or count($ip1) !== 4){
			return false;
		}
		return $ip0[0] === $ip1[0] and $ip0[1] = $ip1[1];
	}
	public function saveData($newStatus = Settings::STATUS_OFFLINE){
		if($this->state === self::STATE_PLAYING){ // don't save if not registered/logged in
			$this->getMain()->saveSessionData($this, $newStatus);
		}
	}
	private function calculateRank(){
		$rank = $this->getRank();
		$prefix = "";
		if($rank & 0x1000) $prefix = "Trial ";
		if($rank & 0x2000) $prefix = "Head ";
		if($rank & 0x0800) return $prefix . "Dev";
		if($rank & 0x0080) return $prefix . "HeadOfStaff";
		if($rank & 0x0040) return $prefix . "Owner";
		if($rank & 0x0020) return $prefix . "Admin";
		if($rank & 0x0010) return $prefix . "Mod";
		if($rank & 0x4000) return "YT";
		$suffix = "";
		if($rank & 1) $suffix = "+";
		if(($rank & 0x000C) === 0x000C) return "VIP$suffix";
		if($rank & 0x0004) return "Donator$suffix";
		return ($suffix === "+") ? "Tester" : "";
	}
	private function sendFirstJoinMessages(){
		$this->getMain()->sendFirstJoinMessages($this->getPlayer());
	}
	public function halfSecondTick(){
		if($this->curPopup !== null){
			$this->getPlayer()->sendPopup($this->curPopup);
		}
		if(time() - $this->joinTime > Settings::KICK_PLAYER_TOO_LONG_ONLINE){
			$this->getPlayer()->kick($this->translate(Phrases::KICK_TOO_LONG_ONLINE));
		}
	}
	/**
	 * @param string $msg
	 * @param int $type
 	 */
	public function onChat($msg, /** @noinspection PhpUnusedParameterInspection */ $type){
		$msg = $this->getChatColor() . preg_replace_callback('/@([A-Za-z_]{3,16})/', function($match){
			if(($player = $this->getMain()->getServer()->getPlayer($match[1])) !== null){
				return $player->getName() . $this->getChatColor();
			}
				return $match[0];
		}, $msg);
		foreach($this->getMain()->getSessions() as $ses){
			// TODO handle $type
			if($ses->isLocalChatOn()){
				$ses->getPlayer()->sendMessage($msg);
			}
		}
	}

	public static function hash($password, $uid){
		return bin2hex(hash("sha512", $password . $uid, true) ^ hash("whirlpool", $uid . $password, true));
	}
}
