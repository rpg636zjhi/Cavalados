<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;

use function array_chunk;
use function array_pop;
use function count;
use function explode;
use function implode;
use function is_numeric;
use function ksort;
use function max;
use function min;
use function strtolower;

use const PHP_INT_MAX;
use const SORT_FLAG_CASE;
use const SORT_NATURAL;

class HelpCommand extends VanillaCommand
{
    public function __construct($name)
    {
        parent::__construct(
            $name,
            "%pocketmine.command.help.description",
            "%commands.help.usage",
            ["?", "ajuda", "comandos"]
        );
        $this->setPermission("pocketmine.command.help");
    }

    public function execute(CommandSender $sender, $currentAlias, array $args)
    {
        if (!$this->testPermission($sender)) {
            return true;
        }

        if (count($args) === 0) {
            $commandName = "";
            $pageNumber = 1;
        } elseif (is_numeric($args[count($args) - 1])) {
            $pageNumber = (int) array_pop($args);
            if ($pageNumber <= 0) {
                $pageNumber = 1;
            }
            $commandName = implode(" ", $args);
        } else {
            $commandName = implode(" ", $args);
            $pageNumber = 1;
        }

        $pageHeight = $sender instanceof ConsoleCommandSender ? PHP_INT_MAX : 6;

        if ($commandName === "") {
            $available = [];
            foreach ($sender->getServer()->getCommandMap()->getCommands() as $command) {
                if ($command->testPermissionSilent($sender)) {
                    $available[$command->getName()] = $command;
                }
            }

            ksort($available, SORT_NATURAL | SORT_FLAG_CASE);
            $pages = array_chunk($available, $pageHeight);
            $pageCount = max(1, count($pages));
            $pageNumber = max(1, (int) min($pageCount, $pageNumber));

            $sender->sendMessage(TextFormat::DARK_GREEN . "======== " . TextFormat::YELLOW . $this->translate($sender, "cavalados.help.title", [$pageNumber, $pageCount]) . TextFormat::DARK_GREEN . " ========");
            if (isset($pages[$pageNumber - 1])) {
                foreach ($pages[$pageNumber - 1] as $command) {
                    $sender->sendMessage(TextFormat::GREEN . "/" . $command->getName() . TextFormat::YELLOW . " - " . TextFormat::AQUA . $command->getDescription());
                }
            }
            $sender->sendMessage(TextFormat::AQUA . $this->translate($sender, "cavalados.help.more"));
            return true;
        }

        $cmd = $sender->getServer()->getCommandMap()->getCommand(strtolower($commandName));
        if ($cmd instanceof Command && $cmd->testPermissionSilent($sender)) {
            $sender->sendMessage(TextFormat::DARK_GREEN . "======== " . TextFormat::YELLOW . "/" . $cmd->getName() . TextFormat::DARK_GREEN . " ========");
            $sender->sendMessage(TextFormat::GREEN . $this->translate($sender, "cavalados.help.description") . ": " . TextFormat::AQUA . $cmd->getDescription());
            $sender->sendMessage(TextFormat::GREEN . $this->translate($sender, "cavalados.help.usage") . ": " . TextFormat::YELLOW . implode("\n" . TextFormat::YELLOW, explode("\n", $cmd->getUsage())));
            if (count($cmd->getAliases()) > 0) {
                $sender->sendMessage(TextFormat::GREEN . $this->translate($sender, "cavalados.help.aliases") . ": " . TextFormat::AQUA . implode(", ", $cmd->getAliases()));
            }
            return true;
        }

        $sender->sendMessage(TextFormat::YELLOW . $this->translate($sender, "cavalados.help.notFound", [strtolower($commandName)]));
        return true;
    }

    private function translate(CommandSender $sender, $key, array $params = [])
    {
        return $sender->getServer()->getLanguage()->translateString($key, $params);
    }
}
