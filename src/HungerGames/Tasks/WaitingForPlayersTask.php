<?php

namespace HungerGames\Tasks;

use HungerGames\Lib\Manager\GameManager;
use HungerGames\Lib\Utils\Msg;
use HungerGames\Loader;
use HungerGames\Object\HungerGames;
use pocketmine\scheduler\Task;

class WaitingForPlayersTask extends Task {

    /** @var Loader */
    private $hungerGamesAPI;
    /** @var HungerGames */
    private $game;
    /** @var GameManager */
    private $manager;
    
    /**
     *
     * WaitingForPlayersTask constructor.
     * @param Loader      $loader
     * @param HungerGames $game
     * 
     */
    public function __construct(Loader $loader, HungerGames $game) {
        $this->hungerGamesAPI = $loader;
        $this->game = $game;
        $this->manager = $loader->getGlobalManager()->getGameManagerByName($game->getName());
    }

    /**
     * 
     * @param int $currentTick
     * 
     */
    public function onRun(int $currentTick) {
        $count = $this->hungerGamesAPI->getStorage()->getAllWaitingPlayersInGameCount($this->game);
        if ($count == 0) {
            $this->manager->setStatus("open");
            $this->hungerGamesAPI->getScheduler()->cancelTask($this->getTaskId());
            $this->manager->refresh();
            return;
        }
        if ($count < $this->game->getMinimumPlayers()) {
            $this->hungerGamesAPI->getScriptManager()->callWhileWaitingForPlayers($this->hungerGamesAPI->getStorage()->getPlayersInWaitingGame($this->game), $this->game);
            $msg = Msg::getHGMessage("hg.message.awaiting");
            $msg = str_replace("%game%", $this->game->getName(), $msg);
            $this->hungerGamesAPI->getGlobalManager()->getGameManagerByName($this->game->getName())->sendGamePopup(Msg::color($msg));
            return;
        }
        if ($count >= $this->game->getMinimumPlayers()) {
            $this->manager->setStatus("waiting");
            $this->hungerGamesAPI->getScheduler()->cancelTask($this->getTaskId());
            $task = new WaitingToStartTask($this->hungerGamesAPI, $this->game);
            $h = $this->hungerGamesAPI->getScheduler()->scheduleRepeatingTask($task, 20);
            $task->setHandler($h);
            return;
        }
    }

}
