<?php

class ClearInventoryOnJoin extends \hungergames\api\scripts\HGAPIScript{

        public function __construct(){
                parent::__construct("ClearInventoryOnJoin", "1.1", "xBeastMode");
        }

        public function onLoad(){
                $this->sendConsoleMessage("Enabling ClearInventoryOnJoin");
        }

        /**
         *
         * @param \pocketmine\Player           $p
         * @param \hungergames\obj\HungerGames $game
         *
         */
        public function onPlayerJoinGame(\pocketmine\Player $p, \hungergames\obj\HungerGames $game){
                $p->getInventory()->clearAll();
        }
}