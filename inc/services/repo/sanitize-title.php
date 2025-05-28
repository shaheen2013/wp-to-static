<?php 

namespace MW_STATIC\Inc\Services\Repo;

class Sanitize_Title{
    public static function sanitize($post_title) {
        $post_title = preg_replace('/\s+/', '-', $post_title);
        
        $post_title = preg_replace('/[^a-zA-Z0-9\-]/', '', $post_title);
        
        return $post_title;
    }
}