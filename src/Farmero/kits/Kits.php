<?php

declare(strict_types=1);

namespace Farmero\kits;

use pocketmine\plugin\PluginBase;

use Farmero\kits\KitsManager;
use Farmero\kits\commands\KitCommand;

class Kits extends PluginBase {

    private $kitsManager;
    public static $instance;
    private $useUI;

    public function onLoad(): void {
        self::$instance = $this;
    }

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->useUI = $this->getConfig()->get("use-ui", true);

        $this->saveResource("kits.yml");
        $this->kitsManager = new KitsManager();
        $this->getServer()->getCommandMap()->register("Kits", new KitCommand());
    }

    public static function getInstance(): self {
        return self::$instance;
    }

    public function getKitsManager(): KitsManager {
        return $this->kitsManager;
    }

    public function isUseUI(): bool {
        return $this->useUI;
    }
}
