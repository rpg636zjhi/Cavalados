<?php

/*
 *
 *    ____ _                   _
 *  / ___| | _____      _____| |_ ___  _ __   ___
 * | |  _| |/ _ \ \ /\ / / __| __/ _ \| '_ \ / _ \
 * | |_| | | (_) \ V  V /\__ \ || (_) | | | |  __/
 *  \____|_|\___/ \_/\_/ |___/\__\___/|_| |_|\___|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Glowstone (Lemdy)
 * @link vk.com/weany
 *
 */

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

use function count;
use function implode;

class ListCommand extends VanillaCommand
{
    public function __construct($name)
    {
        parent::__construct(
            $name,
            "%pocketmine.command.list.description",
            "%command.players.usage",
            ["online", "players", "jogadores"]
        );
        $this->setPermission("pocketmine.command.list");
    }

    public function execute(CommandSender $sender, $currentAlias, array $args)
    {
        if (!$this->testPermission($sender)) {
            return true;
        }

        $players = [];
        foreach ($sender->getServer()->getOnlinePlayers() as $player) {
            if ($player->isOnline() && (!($sender instanceof Player) || $sender->canSee($player))) {
                $players[] = $player->getName();
            }
        }

        $sender->sendMessage(
            TextFormat::DARK_GREEN .
            $sender->getServer()->getLanguage()->translateString("commands.players.list", [count($players), $sender->getServer()->getMaxPlayers()])
        );
        if (count($players) > 0) {
            $sender->sendMessage(TextFormat::YELLOW . implode(TextFormat::GREEN . ", " . TextFormat::YELLOW, $players));
        } else {
            $sender->sendMessage(TextFormat::AQUA . $sender->getServer()->getLanguage()->translateString("cavalados.list.none"));
        }

        return true;
    }
}
