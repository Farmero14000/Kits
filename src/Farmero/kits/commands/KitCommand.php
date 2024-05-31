<?php

declare(strict_types=1);

namespace Farmero\kits\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

use Farmero\kits\Kits;

class KitCommand extends Command {

    public function __construct() {
        parent::__construct("kit");
        $this->setLabel("kit");
        $this->setDescription("Claim a kit");
        $this->setAliases(["k", "kits"]);
        $this->setPermission("kits.cmd.kit");
    }

    public function execute(CommandSender $sender, string $label, array $args): bool {
        if (!$this->testPermission($sender)) {
            return false;
        }

        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game!");
            return true;
        }

        if (!isset($args[0])) {
            $sender->sendMessage("Usage: /kit <kitname>");
            return true;
        }

        $kitName = $args[0];
        if (Kits::getInstance()->getKitsManager()->giveKit($sender, $kitName)) {
            $sender->sendMessage("You have received the $kitName kit!");
        } else {
            $sender->sendMessage("Kit $kitName does not exist...");
        }

        return true;
    }
}