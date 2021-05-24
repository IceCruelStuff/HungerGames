<?php

namespace HungerGames\Lib\Utils;

class Info {

    const AUTHOR = "";
    const VERSION = "1.0.0";
    const API_VERSION = "0.1";
    const FOR_API = ["3.15.0"];
    const CONTRIBUTORS = [];

    /**
     * @return string
     */
    public static function Author() {
        return Info::AUTHOR;
    }

    /**
     * @return string
     */
    public static function Version() {
        return Info::VERSION;
    }

    /**
     * @return string
     */
    public static function APIVersion() {
        return Info::API_VERSION;
    }

    /**
     * @return array
     */
    public static function SoftwareAPIs() {
        return Info::FOR_API;
    }

    /**
     * @return array
     */
    public static function Contributors() {
        return Info::CONTRIBUTORS;
    }

}
