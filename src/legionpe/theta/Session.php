<?php

namespace legionpe\theta;

use legionpe\theta\config\Settings;
use legionpe\theta\utils\MUtils;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

abstract class Session{
	/** @var Player */
	private $player;
	/** @var mixed[] */
	private $loginData;
	public function __construct(Player $player, $loginData){
		$this->player = $player;
		$this->loginData = $loginData;
		if($this->init() === false){
			throw new \Exception;
		}
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
	/**
	 * @return Player
	 */
	public function getPlayer(){
		return $this->player;
	}
	public function onJoin(PlayerJoinEvent $event){

	}
	public function getWarningPoints(){
		return $this->loginData["warnpts"];
	}
	public function getEffectiveConseq(){
		return Settings::getWarnPtsConseq($this->getWarningPoints(), $this->loginData["lastwarn"]);
	}
	public abstract function getMain();
}
