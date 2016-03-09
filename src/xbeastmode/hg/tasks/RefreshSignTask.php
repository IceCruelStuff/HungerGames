<?php
namespace xbeastmode\hg\tasks;
use pocketmine\scheduler\PluginTask;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat as color;

use xbeastmode\hg\HGManagement;
use xbeastmode\hg\Loader;
use xbeastmode\hg\api\HGGame;
use xbeastmode\hg\utils\FMT;
class RefreshSignTask extends PluginTask{
    /** @var Loader */
    private $main;
    /**
     * @param Loader $main
     */
    public function __construct(Loader $main){
        parent::__construct($main);
        $this->main = $main;
    }
    public function onRun($currentTick)
    {
        foreach($this->main->getServer()->getLevels() as $lvl){
            foreach($lvl->getTiles() as $t){
                if($t instanceof Sign){
                    if(strtolower(FMT::b($t->getText()[0])) === strtolower(FMT::b2($this->main->getConfig()->getAll()["sign"]["line1"])) and
                        isset($this->main->getConfig()->getAll()["hg_games"][color::clean($t->getText()[1])])) {

                        $cf = $this->main->getConfig()->getAll();
                        $game = color::clean($t->getText()[1]);
                        $max = HGGame::getApi()->getMaxPlayers($game);
                        if (isset(HGGame::getApi()->onWait[$game]) and isset(HGManagement::$games[$game])) {
                            $l1 = FMT::colorMessage($cf["sign"]["line1"]);
                            $l2 = FMT::colorMessage(str_replace("{game}", $game, $cf["sign"]["line2"]));
                            $l3 = FMT::colorMessage(str_replace(["{count}", "{max}"], [HGGame::getApi()->onWait[$game], $max], $cf["sign"]["line3"]));
                            $l4 = FMT::colorMessage(str_replace("{status}", FMT::colorMessage($cf["status"]["running"]), $cf["sign"]["line4"]));
                            $t->setText($l1, $l2, $l3, $l4);
                        } elseif (!isset(HGGame::getApi()->onWait[$game]) and !isset(HGManagement::$games[$game])){
                            $l1 = FMT::colorMessage($cf["sign"]["line1"]);
                            $l2 = FMT::colorMessage(str_replace("{game}", $game, $cf["sign"]["line2"]));
                            $l3 = FMT::colorMessage(str_replace(["{count}", "{max}"], [0, $max], $cf["sign"]["line3"]));
                            $l4 = FMT::colorMessage(str_replace("{status}", FMT::colorMessage($cf["status"]["waiting"]), $cf["sign"]["line4"]));
                            $t->setText($l1, $l2, $l3, $l4);
                        }elseif(isset(HGGame::getApi()->onWait[$game]) and !isset(HGManagement::$games[$game])){
                            $l1 = FMT::colorMessage($cf["sign"]["line1"]);
                            $l2 = FMT::colorMessage(str_replace("{game}", $game, $cf["sign"]["line2"]));
                            $l3 = FMT::colorMessage(str_replace(["{count}", "{max}"], [HGGame::getApi()->onWait[$game], $max], $cf["sign"]["line3"]));
                            $l4 = FMT::colorMessage(str_replace("{status}", FMT::colorMessage($cf["status"]["waiting"]), $cf["sign"]["line4"]));
                            $t->setText($l1, $l2, $l3, $l4);
                        }
                    }
                }
            }
        }
    }
}
