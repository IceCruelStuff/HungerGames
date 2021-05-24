<?php

namespace HungerGames\Tasks;

use HungerGames\Loader;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class LoadGamesTask extends Task {

    /** @var Loader */
    private $hungerGamesAPI;

    /**
     *
     * LoadGamesTask constructor.
     *
     * @param Loader $loader
     *
     */
    public function __construct(Loader $loader) {
        $this->hungerGamesAPI = $loader;
    }

    /**
     *
     * @param int $currentTick
     *
     */
    public function onRun(int $currentTick) {
        foreach ($this->hungerGamesAPI->getAllGameResources() as $game) {
            $this->hungerGamesAPI->getGlobalManager()->load($game);
        }
        $this->hungerGamesAPI->getLogger()->info(TextFormat::GREEN . "All games have been loaded! At least that's what I think :p");
    }

}
