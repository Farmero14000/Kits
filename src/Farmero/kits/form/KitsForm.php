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
            $kitsManager = Kits::getInstance()->getKitsManager();
            $kitsManager->giveKit($player, $kitName);
        });
        $form->setTitle("Kits");
        $form->setContent("Select a kit to claim:");
        $kitsManager = Kits::getInstance()->getKitsManager();
        foreach ($kitsManager->getKitsConfig()->getAll() as $kitName => $kitData) {
            $displayName = $kitsManager->getKitDisplayName($kitName);
            $form->addButton($displayName);
        }
        $player->sendForm($form);
    }
}
