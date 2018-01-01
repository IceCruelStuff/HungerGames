<?php

namespace hungergames\tasks;

use pocketmine\scheduler\AsyncTask;

class AsyncMapBackup extends AsyncTask{

        private $source, $destination;

        /**
         *
         * AsyncMapBackup constructor.
         *
         * @param string $source
         * @param string $destination
         *
         */
        public function __construct(string $source, string $destination){
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