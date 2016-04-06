<?php
namespace xbeastmode\hg\tasks;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

use xbeastmode\hg\Loader;
use xbeastmode\hg\api\HGGame;
use xbeastmode\hg\utils\FMT;
class MessageTask extends PluginTask{
    /** @var Loader */
    private $main;
    private $game;
    public function __construct(Loader $main, $game){
        parent::__construct($main);
        $this->main = $main;
        $this->game = $game;
    }
    public function onRun($currentTick)
    {
        if(!isset(HGGame::getApi()->players[$this->game])){
            $this->main->getServer()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        foreach(HGGame::getApi()->players[$this->game] as $p){
            if($p instanceof Player){
                $p->sendTip(FMT::colorMessage($this->main->getMessage("waiting_for_players")));
            }
        }
        if(HGGame::getApi()->onWait[$this->game] >= HGGame::getApi()->getMinPlayers($this->game)){
            $this->main->getServer()->getScheduler()->cancelTask($this->getTaskId());
        }
        if(HGGame::getApi()->onWait[$this->game] <= 0){
            $this->main->getServer()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}
