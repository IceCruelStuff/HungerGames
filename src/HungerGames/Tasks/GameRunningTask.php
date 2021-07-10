<?php

namespace HungerGames\Tasks;

use HungerGames\Lib\Manager\GameManager;
use HungerGames\Lib\Utils\Msg;
use HungerGames\Loader;
use HungerGames\Object\HungerGames;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class GameRunningTask extends Task {

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
     * GameRunningTask constructor.
     *
     * @param Loader $loader
     * @param HungerGames $game
     *
     */
    public function __construct(Loader $loader, HungerGames $game) {
        $this->hungerGamesAPI = $loader;
        $this->game = $game;
        $this->seconds = $game->getGameSeconds();
        $this->manager = $loader->getGlobalManager()->getGameManagerByName($game->getName());
    }

    /**
     *
     * @param int $currentTick
     *
     */
    public function onRun(int $currentTick) {
        $count = $this->hungerGamesAPI->getStorage()->getPlayersInGameCount($this->game);
        --$this->seconds;
        if (fmod($this->game->refillAfter(), $this->seconds) == 0) {
            $this->manager->refillChests();
            $msg = Msg::getHungerGamesMessage("hg.message.refill");
            $msg = str_replace("%game%", $this->game->getName(), $msg);
            $this->manager->sendGameMessage($msg);
        }
        if ($count == 0) {
            $this->hungerGamesAPI->getScheduler()->cancelTask($this->getTaskId());
            $this->manager->setStatus("reset");
            $this->manager->refresh();
            $this->game->resetGameLevelBackup();
            $this->hungerGamesAPI->getLogger()->info(TextFormat::GREEN . "Resetting map for game '" . TextFormat::YELLOW . $this->game->getName() . TextFormat::GREEN . "'");
            return;
        }
        if ($count == 1) {
            $this->hungerGamesAPI->getScheduler()->cancelTask($this->getTaskId());
            $this->manager->setStatus("reset");
            foreach ($this->hungerGamesAPI->getStorage()->getPlayersInGame($this->game) as $player) {
                $player->teleport($this->game->getLobbyPosition());
                $player->getInventory()->clearAll();
                $player->getArmorInventory()->clearAll();
                $player->subtractXp($player->getXpDropAmount());
                $player->setXpAndProgress(0, 0);
                $this->hungerGamesAPI->getScriptManager()->callOnPlayerWinGame($player, $this->game);
                $msg = Msg::getHungerGamesMessage("hg.message.win");
                $msg = str_replace(["%game%", "%player%"], [$this->game->getName(), $player->getName()], $msg);
                $this->hungerGamesAPI->getServer()->broadcastMessage(Msg::color($msg));
            }
            $this->hungerGamesAPI->getStorage()->removePlayersInGame($this->game);
            $this->game->resetGameLevelBackup();
            $this->hungerGamesAPI->getLogger()->info(TextFormat::GREEN . "Resetting map for game '" . TextFormat::YELLOW . $this->game->getName() . TextFormat::GREEN . "'");
            return;
        }
        if ($count >= 2 && $this->seconds <= 0) {
            $this->hungerGamesAPI->getScheduler()->cancelTask($this->getTaskId());
            $this->manager->setStatus("reset");
            $this->manager->refresh();
            if ($this->game->isSkyWars() !== "no") {
                $this->hungerGamesAPI->getScriptManager()->callOnGameEnd($this->hungerGamesAPI->getStorage()->getPlayersInGame($this->game), $this->game);
                foreach ($this->hungerGamesAPI->getStorage()->getPlayersInGame($this->game) as $player) {
                    $player->getInventory()->clearAll();
                    $player->teleport($this->game->getLobbyPosition());
                }
                $this->hungerGamesAPI->getStorage()->removePlayersInGame($this->game);
                $msg = Msg::getHungerGamesMessage("hg.message.noWin");
                $msg = str_replace("%game%", $this->game->getName(), $msg);
                $this->hungerGamesAPI->getServer()->broadcastMessage(Msg::color($msg));
                $this->game->resetGameLevelBackup();
                $this->hungerGamesAPI->getLogger()->info(TextFormat::GREEN . "Resetting map for game '" . TextFormat::YELLOW . $this->game->getName() . TextFormat::GREEN . "'");
                return;
            }
            foreach ($this->hungerGamesAPI->getStorage()->getPlayersInGame($this->game) as $player) {
                $player->teleport($this->game->getDeathMatchPosition());
            }
            $msg = Msg::getHungerGamesMessage("hg.message.deathMatch");
            $msg = str_replace("%game%", $this->game->getName(), $msg);
            $this->hungerGamesAPI->getGlobalManager()->getGameManagerByName($this->game->getName())->sendGameMessage(Msg::color($msg));
            $this->hungerGamesAPI->getScriptManager()->callOnDeathMatchStart($this->hungerGamesAPI->getStorage()->getPlayersInGame($this->game), $this->game);
            $task = new DeathMatchTask($this->hungerGamesAPI, $this->game);
            $h = $this->hungerGamesAPI->getScheduler()->scheduleRepeatingTask($task, 20);
            $task->setHandler($h);
            return;
        }

        if ($this->game->isSkyWars() === "no") {
            $msg = Msg::getHungerGamesMessage("hg.message.dmTime");
            $msg = str_replace(["%game%", "%seconds%"], [$this->game->getName(), $this->seconds], $msg);
            $msg = Msg::color($msg);
            $this->manager->sendGamePopup($msg);
        } else {
            $msg = Msg::getHungerGamesMessage("hg.message.swTimeLeft");
            $msg = str_replace(["%game%", "%seconds%"], [$this->game->getName(), $this->seconds], $msg);
            $msg = Msg::color($msg);
            $this->manager->sendGamePopup($msg);
        }
    }

}
