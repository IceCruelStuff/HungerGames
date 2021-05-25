<?php

namespace HungerGames\Lib\Utils;

use RecursiveArrayIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Exc {

    const CHARS = [
        "=", "&", "<", ">", "/", "$", "#", "!", "-", "_", "+", ".", "@",
        "(", ")", "*", "^", "%", ";", ":", "?", "[", "]", "{", "}", "~"
    ];

    /**
     *
     * @param string $string
     * @param array|null $elements
     *
     * @return mixed
     *
     */
    public static function _(string $string, ?array $elements = null) {
        if (is_array($elements)) {
            if (count($elements) == 1) {
                $string = str_replace("$0", $elements[0], $string);
                $string = str_replace("%%", "\xc2\xa7", $string);
                $string = str_replace("%n", "\n", $string);
                return $string;
            }
            for ($i = 0; $i < count($elements); ++$i) {
                $string = str_replace("$$i", $elements[$i], $string);
            }
        }
        $string = str_replace("%%", "\xc2\xa7", $string);
        $string = str_replace("%n", "\n", $string);
        return $string;
    }

    /**
     *
     * @param float $val
     * @param string $exploder
     *
     * @return string
     *
     */
    public static function double(float $val, $exploder = ".") {
        return number_format((float) $val, 2, $exploder, "");
    }

    public static function isNumber($value) {
        return is_numeric($value);
    }

    public static function stringToInteger($val) {
        return self::isNumber($val) ? intval($val) : 0;
    }

    public static function stringToFloat($val) {
        return self::isNumber($val) ? floatval($val) : 0.0;
    }

    /**
     *
     * @param array $val
     *
     * @return null
     *
     */
    public static function randomValue(array $val) {
        if (empty($val)) {
            return null;
        }
        return $val[array_rand($val)];
    }

    /**
     *
     * @param int $length
     *
     * @return int
     *
     */
    public static function randomNumber(int $length) {
        $num = range(0, 9);
        $n = "";
        for ($i = 0; $i < $length; ++$i) {
            $n .= Exc::randomValue($num);
        }
        return intval($n);
    }

    /**
     *
     * @param int $length
     * @param bool|true  $numbers
     * @param bool|false $chars
     *
     * @return string
     *
     */
    public static function randomString(int $length, $numbers = true, $chars = false) {
        $abc = range("A", "Z");
        $num = range(0, 9);
        $str = "";
        if (!$numbers && !$chars) {
            for ($i = 0; $i < $length; ++$i) {
                $str .= Exc::randomValue($abc);
            }
        }
        if ($numbers) {
            for ($i = 0; $i < $length / 2; ++$i) {
                $str .= Exc::randomValue($abc);
                $str .= Exc::randomValue($num);
            }
        }
        if ($chars) {
            for ($i = 0; $i < $length / 2; ++$i) {
                $str .= Exc::randomValue($abc);
                $str .= Exc::randomValue(Exc::CHARS);
            }
        }
        return $str;
    }

    /**
     *
     * @param string $string
     * @param bool|false $numbers
     * @param bool|false $chars
     *
     * @return string
     *
     */
    public static function mixString(string $string, $numbers = false, $chars = false) {
        $num = range(0, 9);
        $str = "";
        if (!$numbers && !$chars) {
            for ($i = 0; $i < strlen($string); ++$i) {
                $str .= $string[$i];
            }
        }
        if ($numbers) {
            for ($i = 0; $i < strlen($string); ++$i) {
                $str .= $string[$i];
                $str .= Exc::randomValue($num);
            }
        }
        if ($chars) {
            for ($i = 0; $i < strlen($string); ++$i) {
                $str .= $string[$i];
                $str .= Exc::randomValue(Exc::CHARS);
            }
        }
        return $str;
    }

    /**
     *
     * @param string $string
     *
     * @return array
     *
     */
    public static function getChars(string $string) {
        preg_match_all("/[[:punct:]]/", $string, $m);
        return $m[0];
    }

    /**
     *
     * @param string $string
     *
     * @return string
     *
     */
    public static function replaceChars(string $string) {
        foreach (Exc::getChars($string) as $char) {
            $string = str_replace($char, "", $string);
        }
        return $string;
    }

    /**
     *
     * @param string $string
     *
     * @return string
     *
     */
    public static function replaceAllKeepLetters(string $string) {
        return preg_replace("![^a-z0-9]+!i", "", $string);
    }

    /**
     *
     * @param string $string
     *
     * @return mixed
     *
     */
    public static function clearColors(string $string) {
        $colors = ["&a", "&b", "&c", "&d", "&e", "&f", "&r", "&k", "&l", "&o"];
        for ($i = 0; $i < 10; ++$i) {
            $string = str_replace("&$i", "", $string);
        }
        foreach ($colors as $c) {
            $string = str_replace($c, "", $string);
        }
        return $string;
    }

    /**
     *
     * @param array $values
     *
     * @return array
     *
     */
    public static function returnArrayOfMultiArray(array $values) {
        $result = [];
        $values = new RecursiveIteratorIterator(new RecursiveArrayIterator($values));
        foreach ($values as $key => $v) {
            $result[$key] = $v;
        }
        return $result;
    }

    /**
     *
     * @param string $filePath
     *
     * @return array
     *
     */
    public static function getFileClasses(string $filePath) {
        $phpCode = file_get_contents($filePath);
        $classes = self::getPHPClasses($phpCode);
        return $classes;
    }

    /**
     *
     * @param string $phpCode
     *
     * @return array
     *
     */
    public static function getPHPClasses(string $phpCode) {
        $classes = [];
        $tokens = token_get_all($phpCode);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING) {
                $className = $tokens[$i][1];
                $classes[] = $className;
            }
        }
        return $classes;
    }

    public static function array_key_exists_md($key, $haystack) {
        $haystack = self::returnArrayOfMultiArray($haystack);
        foreach ($haystack as $r => $v) {
            if ($key === $r || $key === $v) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @param string $dir
     *
     */
    public static function delete(string $dir) {
        if (is_dir($dir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($dir);
        }
    }

}
