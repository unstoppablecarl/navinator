<?php

namespace Navinator;

class StringHelper {

    /**
     * Replaces _ and - with space and uppercases words
     * @param string $str the string to humanize
     */
    static public function humanizeString($str){
        return ucwords(str_replace(array('-', '_'), ' ', $str));
    }

    /**
     * Remove a string from the end of another
     * @param string $suffix the string remove at the end
     * @param String $str the string to modify
     * @return bool
     */
    static public function strRemoveFromEnd($suffix, $str){
        if(substr($str, -strlen($suffix)) === $suffix){
            $str = substr($str, 0, strlen($str) - strlen($suffix));
        }
        return $str;
    }

    /**
     * Checks if a string starts with another string
     * @param string $prefix the string to match at the begining
     * @param String $str the string to check the begining of
     * @return bool
     */
    static public function strStartsWith($prefix, $str){
        return substr($str, 0, strlen($prefix)) === $prefix;
    }

    /**
     * Checks if a string ends with another string
     * @param string $suffix the string to match at the end
     * @param String $str the string to check the begining of
     * @return bool
     */
    static public function strEndsWith($suffix, $str){
        if($suffix === ''){
            return true;
        }
        return substr($str, -strlen($suffix)) === $suffix;
    }

    /**
     * Remove a string from the begining of another
     * @param string $prefix the string remove at the beginging
     * @param String $str the string to modify
     * @return bool
     */
    static public function strRemoveFromBeginning($prefix, $str){
        if(substr($str, 0, strlen($prefix)) === $prefix){
            $str = substr($str, strlen($prefix));
        }
        return $str;
    }
}