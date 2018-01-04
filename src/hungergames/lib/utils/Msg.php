<?php
namespace hungergames\lib\utils;
use hungergames\Loader;
class Msg{
        /** @var array */
        private static $messages = [];
        /** @var bool */
        private static $init = false;

        /**
         * Enable message
         *
         * @return string
         */
        public static function getEnableMessage(){
                $t = str_repeat("=", 50);
                return exc::_("%n%n%n%%e$t%n%n%n%%aYou are using HungerGames version %%6$0!%n%%bMade by %%6$1!%n%n%n%%e$t%n%n%n", [Info::Version(), Info::Author()]);
        }

        /**
         * Gets message by node
         *
         * @param $node
         * @return null|string
         */
        public static function getHGMessage($node){
                if(Msg::$init !== true){
                        Msg::initHGMessages();
                }
                if(isset(Msg::$messages[$node])){
                        return Msg::$messages[$node];
                }
                return null;
        }

        /**
         * Initiates all messages
         */
        public static function initHGMessages(){
                Msg::$messages["hg.message.join"] = self::getConfigMessage('join', self::getDefaultMessage('join'));
                Msg::$messages["hg.message.quit"] = self::getConfigMessage('quit', self::getDefaultMessage('quit'));
                Msg::$messages["hg.message.death"] = self::getConfigMessage('death', self::getDefaultMessage('death'));
                Msg::$messages["hg.message.win"] = self::getConfigMessage('win', self::getDefaultMessage('win'));
                Msg::$messages["hg.message.dmTime"] = self::getConfigMessage('death_match_timer', self::getDefaultMessage('death_match_timer'));
                Msg::$messages["hg.message.awaiting"] = self::getConfigMessage('awaiting_game_tip', self::getDefaultMessage('awaiting_game_tip'));
                Msg::$messages["hg.message.start"] = self::getConfigMessage('game_started', self::getDefaultMessage('game_started'));
                Msg::$messages["hg.message.deathMatch"] = self::getConfigMessage('death_match_started', self::getDefaultMessage('death_match_started'));
                Msg::$messages["hg.message.waiting"] = self::getConfigMessage('waiting_tip', self::getDefaultMessage('waiting_tip'));
                Msg::$messages["hg.message.full"] = self::getConfigMessage('match_full', self::getDefaultMessage('match_full'));
                Msg::$messages["hg.message.running"] = self::getConfigMessage('already_running', self::getDefaultMessage('already_running'));
                Msg::$messages["hg.message.refill"] = self::getConfigMessage('refill_chests', self::getDefaultMessage('refill_chests'));
                Msg::$messages["hg.messages.noWin"] = self::getConfigMessage('no_win', self::getDefaultMessage('no_win'));
                Msg::$messages["hg.messages.swTimeLeft"] = self::getConfigMessage('skywars_time_left', self::getDefaultMessage('skywars_time_left'));
                Msg::$init = true;
        }

        /**
         * Color message
         *
         * @param $message
         * @return string
         */
        public static function color($message){
                $message = str_replace("&0", exc::_("%%0"), $message);
                $message = str_replace("&1", exc::_("%%1"), $message);
                $message = str_replace("&2", exc::_("%%2"), $message);
                $message = str_replace("&3", exc::_("%%3"), $message);
                $message = str_replace("&4", exc::_("%%4"), $message);
                $message = str_replace("&5", exc::_("%%5"), $message);
                $message = str_replace("&6", exc::_("%%6"), $message);
                $message = str_replace("&7", exc::_("%%7"), $message);
                $message = str_replace("&8", exc::_("%%8"), $message);
                $message = str_replace("&9", exc::_("%%9"), $message);
                $message = str_replace("&a", exc::_("%%a"), $message);
                $message = str_replace("&b", exc::_("%%b"), $message);
                $message = str_replace("&c", exc::_("%%c"), $message);
                $message = str_replace("&d", exc::_("%%d"), $message);
                $message = str_replace("&e", exc::_("%%e"), $message);
                $message = str_replace("&f", exc::_("%%f"), $message);
                $message = str_replace("&k", exc::_("%%k"), $message);
                $message = str_replace("&l", exc::_("%%l"), $message);
                $message = str_replace("&m", exc::_("%%m"), $message);
                $message = str_replace("&n", exc::_("%%n"), $message);
                $message = str_replace("&o", exc::_("%%o"), $message);
                $message = str_replace("&r", exc::_("%%r"), $message);
                return $message;
        }

        /**
         * UnColor message
         *
         * @param $message
         * @return mixed
         */
        public static function unColor($message){
                $message = str_replace("&0", "", $message);
                $message = str_replace("&1", "", $message);
                $message = str_replace("&2", "", $message);
                $message = str_replace("&3", "", $message);
                $message = str_replace("&4", "", $message);
                $message = str_replace("&5", "", $message);
                $message = str_replace("&6", "", $message);
                $message = str_replace("&7", "", $message);
                $message = str_replace("&8", "", $message);
                $message = str_replace("&9", "", $message);
                $message = str_replace("&a", "", $message);
                $message = str_replace("&b", "", $message);
                $message = str_replace("&c", "", $message);
                $message = str_replace("&d", "", $message);
                $message = str_replace("&e", "", $message);
                $message = str_replace("&f", "", $message);
                $message = str_replace("&k", "", $message);
                $message = str_replace("&l", "", $message);
                $message = str_replace("&m", "", $message);
                $message = str_replace("&n", "", $message);
                $message = str_replace("&o", "", $message);
                $message = str_replace("&r", "", $message);
                return $message;
        }

        /**
         * Returns customized config messages
         *
         * @param string $key
         * @param string $default
         *
         * @return \string[]
         */
        public static function getConfigMessage(string $key, string $default){
                Loader::getInstance()->pushMessage($key, $default);
                return Loader::getInstance()->getMessages()[$key];
        }

        /**
         * Hg default messages
         *
         * @return array
         */
        public static function getDefaultHGMessages(){
                $cnt =
                    [
                        "join" => "&a%game% > %player% joined.",
                        "quit" => "&e%game% > %player% quit.",
                        "death" => "&c%game% > %player% died. Left players: %left%",
                        "win" => "&a&b%game% > %player% won match!",
                        "no_win" => "&a&b%game% > &aGame is now open!",
                        "death_match_timer" => "&a%game% => %seconds% left till death match.",
                        "awaiting_game_tip" => "&a%game% > &eWaiting for players...",
                        "game_started" => "&a%game% > &aTournament started! Good luck!",
                        "death_match_started" => "&a%game% > &aDeath match started! Good luck!",
                        "waiting_tip" => "&6 > &eStarting game in &b%seconds% &eseconds! &6 <",
                        "match_full" => "&cGame full. Please find a different game !",
                        "already_running" => "&cGame running. Please find a different game !",
                        "refill_chests" => "&a%game% > &eAll chests were refilled!",
                        "skywars_time_left" => "&a%game% > &egame ends in &b%seconds% &eseconds."
                    ];
                return $cnt;
        }

        /**
         * @param string $key
         *
         * @return string|null
         */
        public static function getDefaultMessage(string $key): ?string {
                return self::getDefaultHGMessages()[$key] ?? null;
        }
}
