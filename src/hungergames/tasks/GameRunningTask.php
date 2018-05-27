<?php
namespace hungergames\tasks;
use hungergames\lib\utils\Msg;
use hungergames\Loader;
use hungergames\obj\HungerGames;
use pocketmine\scheduler\PluginTask;
use pocketmine\tile\Chest;
use pocketmine\utils\TextFormat;

class GameRunningTask extends PluginTask{
        /** @var Loader */
        private $HGApi;
        /** @var HungerGames */
        private $game;
        /** @var float */
        private $seconds;

        public function __construct(Loader $main, HungerGames $game){
                parent::__construct($main);
                $this->HGApi = $main;
                $this->game = $game;
                $this->seconds = $game->getGameSeconds();
        }

        /**
         * @param $currentTick
         */
        public function onRun(int $currentTick){
                $count = $this->HGApi->getStorage()->getPlayersInGameCount($this->game);
                --$this->seconds;
                if($this->game->getGameSeconds() - $this->seconds <= $this->game->refillAfter()){
                        foreach($this->game->gameLevel->getTiles() as $tile){
                                if($tile instanceof Chest){
                                        $tile->getInventory()->setContents($this->game->getChestItems());
                                }
                        }
                        $msg = Msg::getHGMessage("hg.message.refill");
                        $msg = str_replace("%game%", $this->game->getName(), $msg);
                        $this->HGApi->getGlobalManager()->getGameManagerByName($this->game->getName())->sendGameMessage($msg);
                }
                if($count == 0){
                        $this->HGApi->getServer()->getScheduler()->cancelTask($this->getTaskId());
                        $this->HGApi->getGlobalManager()->getGameManager($this->game)->setStatus("reset");
                        $this->HGApi->getGlobalManager()->getGameManager($this->game)->refresh();
                        $lvl_path = Loader::getInstance()->getServer()->getDataPath() . "worlds/";
                        $this->HGApi->getMapBackup()->asyncWrite(Loader::getInstance()->dataPath() . "mapBackups/" . $this->game->gameLevel->getFolderName(), $lvl_path . $this->game->gameLevel->getFolderName(), $this->game->getName());
                        $this->HGApi->getLogger()->info(TextFormat::GREEN . "Resetting map for game '" . TextFormat::YELLOW . $this->game->getName() . TextFormat::GREEN . "'");
                        return;
                }
                if($count == 1){
                        $this->HGApi->getServer()->getScheduler()->cancelTask($this->getTaskId());
                        $this->HGApi->getGlobalManager()->getGameManager($this->game)->setStatus("reset");
                        $this->HGApi->getGlobalManager()->getGameManager($this->game)->refresh();
                        foreach($this->HGApi->getStorage()->getPlayersInGame($this->game) as $p){
                                $p->teleport($this->game->getLobbyPosition());
                                $p->getInventory()->clearAll();
                                $this->HGApi->getScriptManager()->callOnPlayerWinGame($p, $this->game);
                                $msg = Msg::getHGMessage("hg.message.win");
                                $msg = str_replace(["%game%", "%player%"], [$this->game->getName(), $p->getName()], $msg);
                                $this->HGApi->getServer()->broadcastMessage(Msg::color($msg));
                        }
                        $this->HGApi->getStorage()->removePlayersInGame($this->game);
                        $this->game->resetGameLevelBackup();
                        $this->HGApi->getLogger()->info(TextFormat::GREEN . "Resetting map for game '" . TextFormat::YELLOW . $this->game->getName() . TextFormat::GREEN . "'");
                        return;
                }
                if($count >= 2 and $this->seconds <= 0){
                        $this->HGApi->getServer()->getScheduler()->cancelTask($this->getTaskId());
                        $this->HGApi->getGlobalManager()->getGameManager($this->game)->setStatus("reset");
                        $this->HGApi->getGlobalManager()->getGameManager($this->game)->refresh();

                        if($this->game->isSkyWars() !== "no"){
                                $this->HGApi->getScriptManager()->callOnGameEnd($this->HGApi->getStorage()->getPlayersInGame($this->game), $this->game);
                                foreach($this->HGApi->getStorage()->getPlayersInGame($this->game) as $p){
                                        $p->getInventory()->clearAll();
                                        $p->teleport($this->game->getLobbyPosition());
                                }
                                $this->HGApi->getStorage()->removePlayersInGame($this->game);
                                $msg = Msg::getHGMessage("hg.message.noWin");
                                $msg = str_replace("%game%", $this->game->getName(), $msg);
                                $this->HGApi->getServer()->broadcastMessage(Msg::color($msg));
                                $this->game->resetGameLevelBackup();
                                $this->HGApi->getLogger()->info(TextFormat::GREEN . "Resetting map for game '" . TextFormat::YELLOW . $this->game->getName() . TextFormat::GREEN . "'");
                                return;
                        }

                        foreach($this->HGApi->getStorage()->getPlayersInGame($this->game) as $p){
                                $p->teleport($this->game->getDeathMatchPosition());
                        }
                        $msg = Msg::getHGMessage("hg.message.deathMatch");
                        $msg = str_replace("%game%", $this->game->getName(), $msg);
                        $this->HGApi->getGlobalManager()->getGameManagerByName($this->game->getName())->sendGameMessage(Msg::color($msg));
                        $this->HGApi->getScriptManager()->callOnDeathMatchStart($this->HGApi->getStorage()->getPlayersInGame($this->game), $this->game);
                        $task = new DeathMatchTask($this->HGApi, $this->game);
                        $h = $this->HGApi->getServer()->getScheduler()->scheduleRepeatingTask($task, 20);
                        $task->setHandler($h);
                        return;
                }

                if($this->game->isSkyWars() === "no"){
                        $msg = Msg::getHGMessage("hg.message.dmTime");
                        $msg = str_replace(["%game%", "%seconds%"], [$this->game->getName(), $this->seconds], $msg);
                        $msg = Msg::color($msg);
                        $this->HGApi->getGlobalManager()->getGameManager($this->game)->sendGamePopup($msg);
                }else{
                        $msg = Msg::getHGMessage("hg.message.swTimeLeft");
                        $msg = str_replace(["%game%", "%seconds%"], [$this->game->getName(), $this->seconds], $msg);
                        $msg = Msg::color($msg);
                        $this->HGApi->getGlobalManager()->getGameManager($this->game)->sendGamePopup($msg);
                }
        }
}
