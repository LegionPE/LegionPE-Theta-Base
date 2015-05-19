<?php

namespace legionpe\theta\queue;

use legionpe\theta\BasePlugin;
use legionpe\theta\config\Settings;
use legionpe\theta\query\SearchServerQuery;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class TransferSearchRunnable implements Runnable{
	/** @var BasePlugin */
	private $plugin;
	/** @var Player */
	private $player;
	/** @var SearchServerQuery */
	private $query;
	public function __construct(BasePlugin $plugin, Player $player, SearchServerQuery $query){
		$this->plugin = $plugin;
		$this->player = $player;
		$this->query = $query;
	}
	public function canRun(){
		return $this->query->hasResult();
	}
	public function run(){
		$result = $this->query->getResult();
		$name = Settings::$CLASSES_NAMES[$this->query->class];
		if(!is_array($result)){
			$this->player->sendMessage(TextFormat::RED . "Error: no servers for $name are online.");
			return;
		}
		/** @var string $ip */
		/** @var int $port */
		extract($result);
		$this->plugin->transfer($this->player, $ip, $port, TextFormat::GREEN . "You are being transferred to $ip:$port ($name server).");
	}
}
