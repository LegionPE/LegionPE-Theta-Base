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
                $session->getPlayer()->sendMessage(TextFormat::RED . "You are already in the local channel");
            } else {
                $session->joinChannel(Session::CHANNEL_LOCAL);
                $session->currentChatState = Session::CHANNEL_LOCAL;
            }
        }
        if (strtolower($args[0]) == "team") {
            if($session->getCurrentChatState() === Session::CHANNEL_TEAM) {
                $session->getPlayer()->sendMessage(TextFormat::RED . "You are already in your team channel");
            } else {
                $session->joinChannel(Session::CHANNEL_TEAM);
                $session->currentChatState = Session::CHANNEL_TEAM;
            }
        }
    }


}