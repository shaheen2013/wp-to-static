<?php 

namespace MW_STATIC\Inc\Services\File;

class Create_Directory{
    public static function create($dir) {
        global $wp_filesystem;
    
        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }
    
        if (!$wp_filesystem->is_dir($dir)) {
            $wp_filesystem->mkdir($dir, FS_CHMOD_DIR);
        }
    }
    
}