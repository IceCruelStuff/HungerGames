<?php
namespace xbeastmode\hg\utils;
####################################################
# ATTENTION!                                       #
# THIS CLASS IS FOR PLUGIN USE ONLY.               #
# IT CAN ALSO BE USED ON PLUGINS WITH CORRECT USE! #
####################################################
use pocketmine\level\Position;
use pocketmine\Player;

class exc{
    ##all chars;;;;;;;;;
    const CHARS = [
        "=", "&", "<", ">", "/", "$", "#", "!", "-", "_", "+", ".", "@",
        "(", ")", "*", "^", "%", ";", ":", "?", "[", "]", "{", "}", "~"
    ];
    private static $t = [];
    public static function _($string, array $elements= null){
        $f = $string;
        if(isset(exc::$t[$f])) $f = exc::$t[$f];
        if (count($elements)) {
            $v = [ "%" => "%" ];
            $i = 0;
            foreach ($elements as $ret) {
                $v["%$i%"] = $ret;
                ++$i;
            }
            $f = strtr($f, $v);
        }
        $f = str_replace("%n", "\n", $f);
        $f = str_replace("%%", "\xc2\xa7", $f);
        return $f;##formatter;;;;;;;;;
    }
    public static function dob($val, $exploder = '.'){
        ##rounds value of number;;;;;;;;;
        return number_format((float)$val, 2, $exploder, '');
    }
    public static function cNum($v){
        ##checks if value is a valid number;;;;;;;;;
        if(is_numeric($v)):return (1|true);
        elseif(is_int($v)):return (1|true);
        elseif(is_float($v)):return (1|true);
        endif;
        return (0|null);
    }
    public static function toN($val){
        ##turn string numbers to int;;;;;;;;;
        $val = intval($val);
        return $val;
    }
    public static function rndAlt(array $val){
        if(
        empty($val)
        )return null;
        ##choose random value from array;;;;;;;;;
        return $val[mt_rand() % (count($val)-1)-(0)];
    }
    public static function rndN($length){
        $num = range(0, 9);
        $n = "";
        for ($i = 0; $i < $length; ++$i) {
            $n .= exc::rndAlt($num);
        }
        return intval($n);
    }
    public static function rndStr($length, $numbers = true, $chars = false){
        ##generates random string;;;;;;;;;
        $abc = range('A', 'Z');
        $num = range(0, 9);
        $str = "";
        if(!$numbers and !$chars){
            for ($i = 0; $i < $length; ++$i) {
                $str .= exc::rndAlt($abc);##chooses random letters from A to Z;;;;;;;;;
            }
        }
        if($numbers) {
            for ($i = 0; $i < $length / 2; ++$i) {
                $str .= exc::rndAlt($abc);##chooses random letters from A to Z;;;;;;;;;
                $str .= exc::rndAlt($num);##chooses random number from 0 to 9;;;;;;;;;
            }
        }
        if($chars){
            for($i = 0; $i < $length / 2; ++$i){
                $str .= exc::rndAlt($abc);##chooses random letters from A to Z;;;;;;;;;
                $str .= exc::rndAlt(exc::CHARS);##chooses random chars;;;;;;;;;
            }
        }
        ##gives random string;;;;;;;;;
        return $str;
    }
    public static function mxStr($string, $numbers = false, $chars = false){
        $num = range(0, 9);
        $str = "";
        if(!$numbers and !$chars){
            for($i = 0; $i < strlen($string); ++$i){
                $str .= $string[$i];##chooses letter ;;;;;;;;;
            }
        }
        if($numbers) {
            for ($i = 0; $i < strlen($string); ++$i) {
                $str .= $string[$i];##chooses letter ;;;;;;;;;
                $str .= exc::rndAlt($num);##chooses number from 0 to 9 ;;;;;;;;;
            }
        }
        if($chars){
            for($i = 0; $i < strlen($string); ++$i){
                $str .= $string[$i];##chooses letter ;;;;;;;;;
                $str .= exc::rndAlt(exc::CHARS);##chooses random chars;;;;;;;;;
            }
        }
        return $str;
    }
    public static function cngCh($string){
        preg_match_all("/[[:punct:]]/", $string, $m);
        return $m[0];##gets all types of chars;;;;;;;;;
    }
    public static function rCh($string){
        foreach(exc::cngCh($string) as $char){
            $string = str_replace($char, "", $string);
        }
        return $string;##replaces chars in string;;;;;;;;;
    }

    public static function mirrorY(Player $p, $farness = 2){
        return new Position($p->x-$farness, $p->y, $p->z);##PLUGIN USE;;;;;;;;;
    }
    public static function mirrorX(Player $p, $farness = 2){
        return new Position($p->x-$farness, $p->y-$farness, $p->z);##PLUGIN USE;;;;;;;;;
    }
}