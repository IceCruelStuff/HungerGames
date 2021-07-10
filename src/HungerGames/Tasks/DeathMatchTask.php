<?php

namespace HungerGames\Tasks;

use HungerGames\Lib\Utils\Msg;
use HungerGames\Loader;
use HungerGames\Object\HungerGames;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class DeathMatchTask extends Task {

    /** @var Loader */
    private $hungerGamesAPI;
    /** @var HungerGames */
    private $game;

    public function __construct(Loader $loader, HungerGames $game) {
        $this->hungerGamesAPI = $loader;
        $this->game = $game;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        $count = $this->hungerGamesAPI->getStorage()->getPlayersInGameCount($this->game);
        if ($count < 2) {
            $this->hungerGamesAPI->getScheduler()->cancelTask($this->getTaskId());
            $this->hungerGamesAPI->getGlobalManager()->getGameManager($this->game)->setStatus("reset");
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
            $this->hungerGamesAPI->getGlobalManager()->getGameManager($this->game)->refresh();
            return;
        }
    }

}
