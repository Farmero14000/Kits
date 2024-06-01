<?php

declare(strict_types=1);

namespace Farmero\kits\form;

use pocketmine\player\Player;

use jojoe77777\FormAPI\SimpleForm;

use Farmero\kits\Kits;

class KitsForm {

    public static function sendKitsForm(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) {
                return;
            }

            $kitName = array_keys(Kits::getInstance()->getKitsManager()->getKitsConfig()->getAll())[$data];
            if (!Kits::getInstance()->getKitsManager()->giveKit($player, $kitName)) {
                $cooldownMessage = Kits::getInstance()->getKitsManager()->formatCooldownMessage(
                    Kits::getInstance()->getKitsManager()->getRemainingCooldown($player, $kitName)
                );
                $player->sendMessage("Failed to claim $kitName. Cooldown remaining: $cooldownMessage");
            } else {
                $player->sendMessage("You have received the $kitName kit!");
            }
        });

        $form->setTitle("Kits");
        $form->setContent("Select a kit to claim:");

        $kitsManager = Kits::getInstance()->getKitsManager();
        foreach ($kitsManager->getKitsConfig()->getAll() as $kitName => $kitData) {
            $form->addButton($kitName);
        }

        $player->sendForm($form);
    }
}