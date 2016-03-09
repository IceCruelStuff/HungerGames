<?php
namespace xbeastmode\hg;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

use xbeastmode\hg\commands\hgCmd;
use xbeastmode\hg\api\EndGame;
use xbeastmode\hg\tasks\MessageTask;
use xbeastmode\hg\tasks\RefreshSignTask;
use xbeastmode\hg\tasks\WaitingTask;
use xbeastmode\hg\api\HGGame;
use xbeastmode\hg\tasks\GameTask;
class Loader extends PluginBase{
    /** @var array */
    public $tasks = [];
    /** @var EndGame */
    public $e;
    /** @var Config*/
    private $msg;
    public function onEnable(){
        $messages =
            [
                "joined_game" => "&a[HG][%game%] %player% joined the match.",
                "already_running" => "&c[HG] %game% is already running. Please find another one.",
                "not_enough_players" => "&c[HG] cannot start game because there are not enough players.",
                "match_started" => "&a[HG] match &6%game% started! Starting death match in &b%minutes% minutes!",
                "death_match_started" => "&a[HG] Death-match started! Good luck.",
                "game_open" => "&a[HG] &e%game% &ais open.",
                "waiting_for_players" => "&aWaiting for players...",
                "waiting_tip" => "&f> &eStarting in %seconds% seconds &f<",
                "won_match" => "&a[HG] &b%player% won the match %game%!",
                "match_full" => "&c[HG match is full."
            ];
        (new Events($this));
        (new HGGame($this));
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->msg = new Config($this->getDataFolder()."messages.yml", Config::YAML, $messages);
        $this->getServer()->getCommandMap()->register("hg", new hgCmd($this));
        $this->getServer()->getPluginManager()->registerEvents(new Events($this), $this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new RefreshSignTask($this), 20);
        $this->e = (new EndGame($this));
    }
    /**
     * @param $msg
     * @return string
     */
    public function getMessage($msg){
        return $this->msg->get($msg);
    }

    //NON API PART

    public function createWaitingTask($game){
        if(isset($this->tasks[$game])) return;
        $time = HGGame::getApi()->getWaitingTime($game);
        $t = new WaitingTask($this, $game, $time);
        $h = $this->getServer()->getScheduler()->scheduleRepeatingTask($t, 20);
        $t->setHandler($h);
        $this->tasks[$game] = $t->getTaskId();
    }
    public function createGameTask($game){
        $time = HGGame::getApi()->getGameTime($game);
        $t = new GameTask($this, $game);
        $h = $this->getServer()->getScheduler()->scheduleDelayedTask($t, 20*$time);
        $t->setHandler($h);
        $this->tasks[$game] = $t->getTaskId();
    }
    public function createMessageTask($game){
        $t = new MessageTask($this, $game);
        $h = $this->getServer()->getScheduler()->scheduleRepeatingTask($t, 20);
        $t->setHandler($h);
    }
}
