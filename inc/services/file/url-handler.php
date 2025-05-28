<?php 

namespace MW_STATIC\Inc\Services\File;

class Url_Handler{

    public static function resolve_url($base_url, $relative_url) {
        $parsed_base = wp_parse_url($base_url);
        $parsed_relative = wp_parse_url($relative_url);
    
        if (strpos($relative_url, 'fonts.googleapis.com') !== false) {
            return $relative_url;
        }
    
        if (strpos($relative_url, '//') === 0) {
            $scheme = $parsed_base['scheme'];
            $relative_url = $scheme . ':' . $relative_url;
        }
    
        if (isset($parsed_relative['host'])) {
            $clean_url = strtok($relative_url, '?');
            return strtok($clean_url, '#');
        }
    
        $scheme = $parsed_base['scheme'] . '://';
        $host = $parsed_base['host'];
        $path = isset($parsed_base['path']) ? rtrim(dirname($parsed_base['path']), '/') . '/' : '/';
    
        $resolved_url = $scheme . $host . $path . ltrim($relative_url, '/');
    
        $clean_url = strtok($resolved_url, '?');
        return strtok($clean_url, '#');
    }
    
    
    
}