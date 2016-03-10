<?php
namespace xbeastmode\hg\tasks;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

use xbeastmode\hg\HGManagement;
use xbeastmode\hg\Loader;
use xbeastmode\hg\api\HGGame;
use xbeastmode\hg\utils\FMT;
class GameTask extends PluginTask{
    private $game;
    /** @var Loader */
    private $main;
    public function __construct(Loader $main, $game){
        parent::__construct($main);
        $this->main = $main;
        $this->game = $game;
    }
    /**
     * Actions to execute when run
     *
     * @param $currentTick
     *
     * @return void
     */
    public function onRun($currentTick)
    {
        if(!isset(HGGame::getApi()->players[$this->game])){
            $this->main->getServer()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        foreach(HGGame::getApi()->players[$this->game] as $p){
            if(HGGame::getApi()->onWait[$this->game] >= 2 and $p instanceof Player and isset(HGManagement::$games[$this->game])) {
                $match = $this->main->getConfig()->getAll()["hg_games"][$this->game]["death_match_pos"];
                $level = $this->main->getServer()->getLevelByName($match["level"]);
                $p->sendMessage(FMT::colorMessage(str_replace("%game%", $this->game, $this->main->getMessage("death_match_started"))));
                $p->teleport(new Position($match["x"], $match[1], $match["z"], $level), $p->getYaw(), $p->getPitch());
                }elseif(HGGame::getApi()->onWait[$this->game] <= 1 and $p instanceof Player){
                    $this->main->getServer()->getScheduler()->cancelTask($this->getTaskId());
                    $this->main->e->deleteGameData($this->game);
                    $match = $this->main->getConfig()->getAll()["hg_games"][$this->game]["lobby_pos"];
                    $level = $this->main->getServer()->getLevelByName($match["level"]);
                    $p->teleport(new Position($match["x"], $match[1], $match["z"], $level), 0, 0);
                    $this->main->getServer()->broadcastMessage(FMT::colorMessage(str_replace("%game%", $this->game, $this->main->getMessage("game_open"))));
                    $p->sendMessage(FMT::colorMessage($this->main->getMessage("not_enough_players")));
                break;
            }
        }
        if(!isset(HGGame::getApi()->players[$this->game])){
            $this->main->getServer()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        if(HGGame::getApi()->onWait[$this->game] <= 1){
            $this->main->getServer()->getScheduler()->cancelTask($this->getTaskId());
            foreach(HGGame::getApi()->players[$this->game] as $p) {
                if($p instanceof Player) {
                    $this->main->e->deleteGameData($this->game);
                    $match = $this->main->getConfig()->getAll()["hg_games"][$this->game]["lobby_pos"];
                    $level = $this->main->getServer()->getLevelByName($match["level"]);
                    $p->teleport(new Position($match["x"], $match[1], $match["z"], $level), 0, 0);
                }
            }
            $this->main->getServer()->broadcastMessage(FMT::colorMessage(str_replace("%game%", $this->game, $this->main->getMessage("game_open"))));
        }
    }
}
