<?php
namespace xbeastmode\hg\event\player;
use pocketmine\event\Cancellable;
use pocketmine\Player;
use xbeastmode\hg\event\HGEvent;
use xbeastmode\hg\Loader;
class PlayerQuitGameEvent extends HGEvent implements Cancellable{
    public static $handlerList = null;

    /** @var Player */
    private $player;
    private $game;
    /**
     * @param Loader $main
     * @param Player $p
     * @param $game
     */
    public function __construct(Loader $main, Player $p, $game){
        parent::__construct($main);
        $this->player = $p;
        $this->game = $game;
    }
    /**
     * @return Player
     */
    public function getPlayer(){
        return $this->player;
    }
    /**
     * @param Player $p
     */
    public function setPlayer(Player $p){
        $this->player = $p;
    }
    /**
     * @return string
     */
    public function getGame(){
        return $this->game;
    }
}
