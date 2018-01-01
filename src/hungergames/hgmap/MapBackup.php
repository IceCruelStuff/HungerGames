<?php
namespace hungergames\hgmap;
use hungergames\Loader;
use hungergames\tasks\AsyncMapBackup;

class MapBackup{

        /** @var Loader */
        public $loader;

        /**
         *
         * MapBackup constructor.
         *
         * @param Loader $loader
         *
         */
        public function __construct(Loader $loader){
                $this->loader = $loader;
        }

        /**
         *
         * Writes folder backup
         *
         * @param $source
         * @param $destination
         *
         * @return void
         *
         */
        public function write($source, $destination){
                $this->asyncWrite($source, $destination);
        }


        /**
         *
         * @param $source
         * @param $destination
         *
         */
        public function asyncWrite($source, $destination){
                $this->loader->getServer()->getScheduler()->scheduleAsyncTask(new AsyncMapBackup($source, $destination));
        }

        /**
         *
         * Resets game map
         *
         * @param $source
         * @param $destination
         *
         * @return void
         *
         */
        public function reset($source, $destination){
                $this->asyncWrite($source, $destination);
        }

}