<?php

declare(strict_types=1);

namespace Farmero\kits\task;

use pocketmine\scheduler\Task;

use Farmero\kits\Kits;

class KitCooldownTask extends Task {

    public function onRun(): void {
        $this->checkCooldowns();
    }

    private function checkCooldowns(): void {
        $currentTime = time();
        foreach (Kits::getInstance()->getKitsManager()->getCooldowns() as $playerName => $kits) {
            foreach ($kits as $kitName => $cooldownTime) {
                $kitData = Kits::getInstance()->getKitsManager()->getKit($kitName);
                if ($kitData !== null && isset($kitData["cooldown"]) && $currentTime >= $cooldownTime) {
                    Kits::getInstance()->getKitsManager()->removeCooldown($playerName, $kitName);
                }
            }
        }
    }
}
