<?php

namespace HungerGames\Tasks;

use HungerGames\Loader;
use pocketmine\scheduler\Task;

class RefreshSignsTask extends Task {

    /** @var Loader */
    private $hungerGamesAPI;

    /**
     *
     * RefreshSignsTask constructor.
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
        $this->hungerGamesAPI->getSignManager()->refreshAllSigns();
    }

}
