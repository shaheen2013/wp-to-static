<?php 

namespace MW_STATIC\Inc\Helper;

class Extract_Domain_Name{
    public static function extract($url){
        $parsedUrl = wp_parse_url($url);
        if (isset($parsedUrl['host'])) {
            $host = $parsedUrl['host'];
            return preg_replace('/^www\./', '', $host);
        }
        return null;
    }
}