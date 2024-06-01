<?php

declare(strict_types=1);

namespace Farmero\kits\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

use Farmero\kits\Kits;
use Farmero\kits\form\KitsForm;

class KitCommand extends Command {

    public function __construct() {
        parent::__construct("kit");
        $this->setLabel("kit");
        $this->setDescription("Claim a kit");
        $this->setAliases(["k", "kits"]);
        $this->setPermission("kits.cmd.kit");
    }

    public function execute(CommandSender $sender, string $label, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game!");
            return true;
        }

        $kitsManager = Kits::getInstance()->getKitsManager();

        if (Kits::getInstance()->isUseUI()) {
            KitsForm::sendKitsForm($sender);
            return true;
        } else {
            if (!isset($args[0])) {
                $sender->sendMessage("Usage: /kit <kitname>");
                return true;
            }

            $kitName = $args[0];

            if (!$kitsManager->kitExists($kitName)) {
                $sender->sendMessage("Kit $kitName does not exist...");
                return true;
            }

            if (!$kitsManager->giveKit($sender, $kitName)) {
                $cooldownMessage = $this->getCooldownMessage($sender, $kitName);
                if ($cooldownMessage !== null) {
                    $sender->sendMessage("Failed to claim $kitName. Cooldown remaining: $cooldownMessage");
                } else {
                    $sender->sendMessage("Kit $kitName does not exist...");
                }
            } else {
                $sender->sendMessage("You have received the $kitName kit!");
            }
            return true;
        }
    }

    private function getCooldownMessage(Player $player, string $kitName): ?string {
        $remainingCooldown = Kits::getInstance()->getKitsManager()->getRemainingCooldown($player, $kitName);
        if ($remainingCooldown > 0) {
            return Kits::getInstance()->getKitsManager()->formatCooldownMessage($remainingCooldown);
        }
        return null;
    }
}
