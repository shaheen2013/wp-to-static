<?php 

namespace MW_STATIC\Inc\Helper;

class Urls_Replacer{
    public static function replace($contents, array $urls_array){

        foreach ($urls_array as $find => $replace) {
            $contents = str_replace($find, $replace, $contents);
        }
        return $contents;
    }
}