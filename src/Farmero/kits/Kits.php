<?php

declare(strict_types=1);

namespace Farmero\kits;

use pocketmine\plugin\PluginBase;

use Farmero\kits\KitsManager;

use Farmero\kits\commands\KitCommand;

use Farmero\kits\task\KitCooldownTask;

class Kits extends PluginBase {

    private $kitsManager;
    public static $instance;

    public function onLoad(): void {
        self::$instance = $this;
    }

    public function onEnable(): void {
        $this->saveResource("kits.yml");
        $this->kitsManager = new KitsManager();
        $this->getServer()->getCommandMap()->register("Kits", new KitCommand());
        $this->getScheduler()->scheduleRepeatingTask(new KitCooldownTask(), 20);
    }

    public static function getInstance(): self {
        return self::$instance;
    }

    public function getKitsManager(): KitsManager {
        return $this->kitsManager;
    }
}
