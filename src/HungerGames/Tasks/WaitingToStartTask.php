<?php

namespace HungerGames\Tasks;

use HungerGames\Lib\Manager\GameManager;
use HungerGames\Lib\Utils\Msg;
use HungerGames\Loader;
use HungerGames\Object\HungerGames;
use pocketmine\block\Block;
use pocketmine\scheduler\Task;

class WaitingToStartTask extends Task {

    /** @var Loader */
    private $hungerGamesAPI;
    /** @var HungerGames */
    private $game;
    /** @var float */
    private $seconds;
    /** @var GameManager */
    private $manager;

    /**
     *
     * WaitingToStartTask constructor.
     *
     * @param Loader      $loader
     * @param HungerGames $game
     *
     */
    public function __construct(Loader $loader, HungerGames $game) {
        $this->hungerGamesAPI = $loader;
        $this->game = $game;
        $this->seconds = $game->getWaitingSeconds();
        $this->manager = $loader->getGlobalManager()->getGameManagerByName($game->getName());
    }

    /**
     *
     * @param int $currentTick
     *
     */
    public function onRun(int $currentTick) {
        $count = $this->hungerGamesAPI->getStorage()->getAllWaitingPlayersInGameCount($this->game);
        --$this->seconds;
        if ($count == 0) {
            $this->manager->setStatus("open");
            $this->hungerGamesAPI->getScheduler()->cancelTask($this->getTaskId());
            $this->manager->refresh();
            return;
        }
        if ($this->seconds > 0) {
            if ($count >= $this->game->getMinimumPlayers()) {
                $message = str_replace(["%seconds%", "%game%"], [$this->seconds, $this->game->getName()], Msg::getHungerGamesMessage("hg.message.waiting"));
                $this->manager->sendGamePopup(Msg::color($message));
                $this->hungerGamesAPI->getScriptManager()->callWhileWaitingToStart($this->hungerGamesAPI->getStorage()->getPlayersInWaitingGame($this->game), $this->game);
                return;
            }
            if ($count < $this->game->getMinimumPlayers()) {
                $this->manager->setStatus("waiting");
                $this->hungerGamesAPI->getScheduler()->cancelTask($this->getTaskId());
                $task = new WaitingForPlayersTask($this->hungerGamesAPI, $this->game);
                $h = $this->hungerGamesAPI->getScheduler()->scheduleRepeatingTask($task, 20);
                $task->setHandler($h);
                return;
            }
            return;
        }
        if ($this->seconds == 0 && $count >= $this->game->getMinimumPlayers()) {
            $task = new GameRunningTask($this->hungerGamesAPI, $this->game);
            $h = $this->hungerGamesAPI->getScheduler()->scheduleRepeatingTask($task, 20);
            $task->setHandler($h);
            foreach ($this->hungerGamesAPI->getStorage()->getPlayersInWaitingGame($this->game) as $player) {
                if($player->getLevel()->getBlock($player->subtract(0, 1))->getId() === Block::GLASS){
                    $player->getLevel()->setBlock($player->subtract(0, 1), Block::get(0));
                }
            }
            $this->manager->setStatus("running");
            $this->manager->refillChests();
            foreach ($this->hungerGamesAPI->getStorage()->getPlayersInWaitingGame($this->game) as $player) {
                $this->hungerGamesAPI->getStorage()->addPlayer($player, $this->game);
            }
            $this->hungerGamesAPI->getStorage()->removePlayersInWaitingGame($this->game);
            $this->hungerGamesAPI->getScheduler()->cancelTask($this->getTaskId());
            $message = str_replace("%game%", $this->game->getName(), Msg::getHungerGamesMessage("hg.message.start"));
            $this->manager->sendGameMessage(Msg::color($message));
            $this->hungerGamesAPI->getScriptManager()->callOnGameStart($this->hungerGamesAPI->getStorage()->getPlayersInGame($this->game), $this->game);
            return;
        }
    }

}
