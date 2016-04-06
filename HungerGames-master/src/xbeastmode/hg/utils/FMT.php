<?php
namespace xbeastmode\hg\utils;
use pocketmine\utils\TextFormat as color;
class FMT{
    /**
     * Function By @EvolSoft
     * Take from PerWorldChat plugin
     *
     * @param $message
     * @return string
     */
    public static function colorMessage($message){
        $symbol = "&";
        $message = str_replace($symbol."0", color::BLACK, $message);
        $message = str_replace($symbol."1", color::DARK_BLUE, $message);
        $message = str_replace($symbol."2", color::DARK_GREEN, $message);
        $message = str_replace($symbol."3", color::DARK_AQUA, $message);
        $message = str_replace($symbol."4", color::DARK_RED, $message);
        $message = str_replace($symbol."5", color::DARK_PURPLE, $message);
        $message = str_replace($symbol."6", color::GOLD, $message);
        $message = str_replace($symbol."7", color::GRAY, $message);
        $message = str_replace($symbol."8", color::DARK_GRAY, $message);
        $message = str_replace($symbol."9", color::BLUE, $message);
        $message = str_replace($symbol."a", color::GREEN, $message);
        $message = str_replace($symbol."b", color::AQUA, $message);
        $message = str_replace($symbol."c", color::RED, $message);
        $message = str_replace($symbol."d", color::LIGHT_PURPLE, $message);
        $message = str_replace($symbol."e", color::YELLOW, $message);
        $message = str_replace($symbol."f", color::WHITE, $message);
        $message = str_replace($symbol."k", color::OBFUSCATED, $message);
        $message = str_replace($symbol."l", color::BOLD, $message);
        $message = str_replace($symbol."m", color::STRIKETHROUGH, $message);
        $message = str_replace($symbol."n", color::UNDERLINE, $message);
        $message = str_replace($symbol."o", color::ITALIC, $message);
        $message = str_replace($symbol."r", color::RESET, $message);
        return $message;
    }
    /**
     * @param $message
     * @return string
     */
    public static function b($message){
        return color::clean($message, true);
    }
    /**
     * @param $message
     * @return string
     */
    public static function b2($message){
        $symbol = "&";
        $message = str_replace($symbol."0", "", $message);
        $message = str_replace($symbol."1", "", $message);
        $message = str_replace($symbol."2", "", $message);
        $message = str_replace($symbol."3", "", $message);
        $message = str_replace($symbol."4", "", $message);
        $message = str_replace($symbol."5", "", $message);
        $message = str_replace($symbol."6", "", $message);
        $message = str_replace($symbol."7", "", $message);
        $message = str_replace($symbol."8", "", $message);
        $message = str_replace($symbol."9", "", $message);
        $message = str_replace($symbol."a", "", $message);
        $message = str_replace($symbol."b", "", $message);
        $message = str_replace($symbol."c", "", $message);
        $message = str_replace($symbol."d", "", $message);
        $message = str_replace($symbol."e", "", $message);
        $message = str_replace($symbol."f", "", $message);
        $message = str_replace($symbol."k", "", $message);
        $message = str_replace($symbol."l", "", $message);
        $message = str_replace($symbol."m", "", $message);
        $message = str_replace($symbol."n", "", $message);
        $message = str_replace($symbol."o", "", $message);
        $message = str_replace($symbol."r", "", $message);
        return $message;
    }
}
