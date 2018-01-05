<?php

namespace hungergames\tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class AsyncMapBackup extends AsyncTask{

        private $source, $destination, $game;

        /**
         *
         * AsyncMapBackup constructor.
         *
         * @param string $source
         * @param string $destination
         * @param string $game
         */
        public function __construct(string $source, string $destination, string $game){
                $this->source = $source;
                $this->destination = $destination;
        }

        /**
         * Actions to execute when run
         *
         * @return void
         */
        public function onRun(){
                $this->write($this->source, $this->destination);
        }

        /**
         *
         * @param Server $server
         *
         */
        public function onCompletion(Server $server){
                $mgr = $server->getPluginManager()->getPlugin("HungerGames")->getGlobalManager()->getGameManagerByName($this->game);
                if($mgr !== null){
                        $mgr->setStatus("open");
                }
        }

        /**
         *
         * @param $source
         * @param $destination
         *
         */
        public function write($source, $destination){
                $dir = opendir($source);
                @mkdir($destination);
                while(false !== ($file = readdir($dir))){
                        if(($file != '.') && ($file != '..')){
                                if(is_dir($source . '/' . $file)){
                                        $this->write($source . '/' . $file, $destination . '/' . $file);
                                }else{
                                        copy($source . '/' . $file, $destination . '/' . $file);
                                }
                        }
                }
                closedir($dir);
        }
}