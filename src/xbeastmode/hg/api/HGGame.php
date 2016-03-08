<?php
namespace xbeastmode\hg\api;
use pocketmine\level\Position;
use pocketmine\Player;
use xbeastmode\hg\Loader;
use xbeastmode\hg\utils\FMT;
class HGGame{
    /** @var array */
    public $players = [];
    /** @var array */
    public $onWait = [];
    /** @var array */
    public $counters = [];
    /** @var Loader */
    public $main;
    /**
     * @var HGGame
     */
    private static $object;
    /**
     * @param Loader $main
     */
    public function __construct(Loader $main){
        $this->main = $main;
        if(!self::$object instanceof HGGame){
            self::$object = $this;
        }
    }
    public static function getApi(){
        return self::$object;
    }
    /**
     * @param $game
     * @return int|null
     */
    public function getMinPlayers($game){
        if($this->main->getConfig()->getAll()["hg_games"][$game]["min_players"] !== null) {
            return $this->main->getConfig()->getAll()["hg_games"][$game]["min_players"];
        }
        return 0;
    }
    /**
     * @param $game
     * @return int|null
     */
    public function getMaxPlayers($game){
        if($this->main->getConfig()->getAll()["hg_games"][$game]["max_players"] !== null) {
            return $this->main->getConfig()->getAll()["hg_games"][$game]["max_players"];
        }
        return 0;
    }
    /**
     * @param $game
     * @return int
     */
    public function getGameTime($game){
        return $this->main->getConfig()->getAll()["hg_games"][$game]["game_time"];
    }
    /**
     * @param $game
     * @return int
     */
    public function getWaitingTime($game){
        return $this->main->getConfig()->getAll()["hg_games"][$game]["wait_time"];
    }
    /**
     * @param $game
     * @return \pocketmine\level\Level
     */
    public function getGameLevel($game){
        return $this->main->getServer()->getLevelByName($this->main->getConfig()->getAll()["hg_games"][$game]["level"]);
    }
    /**
     * @param $game
     * @return Position
     */
    public function getLobbyPosition($game){
        $pos = $this->main->getConfig()->getAll()["hg_games"][$game]["lobby_pos"];
        $level = $this->main->getServer()->getLevelByName($pos["level"]);
        return new Position($pos["x"], $pos[1], $pos["z"], $level);
    }
    /**
     * @param $game
     * @return Position
     */
    public function getDeathMatchPosition($game){
        $pos = $this->main->getConfig()->getAll()["hg_games"][$game]["death_match_pos"];
        $level = $this->main->getServer()->getLevelByName($pos["level"]);
        return new Position($pos["x"], $pos[1], $pos["z"], $level);
    }
    /**
     * @param Player $p
     * @param $game
     * @return bool
     */
    public function tpToOpenSlot(Player $p, $game)
    {
        $this->main->getServer()->loadLevel($this->main->getConfig()->getAll()["hg_games"][$game]["level"]);
        if(!isset($this->counters[$game])){
            $this->counters[$game] = count($this->main->getConfig()->getAll()["hg_games"][$game]["slots"]);
        }
        if($this->counters[$game] == 0){
            $this->main->getLogger()->notice(FMT::colorMessage("&eGame &b$game &ehas empty slots."));
            $p->sendMessage(FMT::colorMessage("&eAn error occurred while joining game, please contact an admin."));
            return false;
        }
        $pos = $this->main->getConfig()->getAll()["hg_games"][$game]["slots"]["slot".$this->counters[$game]];
        $level = $this->main->getServer()->getLevelByName($this->main->getConfig()->getAll()["hg_games"][$game]["level"]);
        $p->teleport(new Position($pos["x"], $pos[1], $pos["z"], $level));
        $this->counters[$game] -= 1;
        $this->players[$game][spl_object_hash($p)] = $p;
        if(!isset($this->onWait[$game])){
            $this->onWait[$game] = 0;
        }
        $this->onWait[$game] += 1;
        if($this->onWait[$game] >= $this->getMinPlayers($game)){
            $this->main->createWaitingTask($game);
        }
        if($this->onWait[$game] < $this->getMinPlayers($game)){
            $this->main->createMessageTask($game);
        }
        if($this->counters[$game] <= -1){
            $p->sendMessage(FMT::colorMessage($this->main->getMessage("match_full")));
        }
        return true;
    }
}
