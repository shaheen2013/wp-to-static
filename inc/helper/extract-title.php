<?php 

namespace MW_STATIC\Inc\Helper;

class Extract_Title{
    public static function extract($url) {
        $parsedUrl = wp_parse_url($url);
    
        if (!isset($parsedUrl['host'])) {
            return null;
        }
    
        if (isset($parsedUrl['path'])) {
            $path = trim($parsedUrl['path'], '/');
            $segments = explode('/', $path);
            return end($segments);
        }
    
        return null;
    }
}