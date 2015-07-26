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

namespace legionpe\theta\command\session;

use legionpe\theta\BasePlugin;

use legionpe\theta\Session;
use legionpe\theta\command\SessionCommand;

use pocketmine\utils\TextFormat;

class ChannelCommand extends SessionCommand {

    public function __construct(BasePlugin $plugin) {
        parent::__construct($plugin, "channel", "switch channels", "/channel <channel...>");
    }
    public function run(array $args, Session $session)
    {
        if (strtolower($args[0]) == "local") {
            if($session->getCurrentChatState() === Session::CHANNEL_LOCAL) {
                $session->getPlayer()->sendMessage(TextFormat::RED . "The channel state is already local");
            } else {
                $session->currentChatState = Session::CHANNEL_LOCAL;
            }
        }
        if (strtolower($args[0]) == "team") {
            if($session->getCurrentChatState() === Session::CHANNEL_TEAM) {
                $session->getPlayer()->sendMessage(TextFormat::RED . "The channel state is already the team");
            } else {
                $session->currentChatState = Session::CHANNEL_TEAM;
            }
        }
        if(!isset($args[0])){
	        return false;
	} else {
            $channel = array_shift($args);
            $session->joinChannel($channel);
            return $session->getPlayer()->sendMessage(TextFormat::AQUA . "You have joined the channel " . $channel);
        }
    }


}
