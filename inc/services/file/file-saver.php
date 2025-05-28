<?php 

namespace MW_STATIC\Inc\Services\File;

class File_Saver{

    public static function save($content, $path) {
        global $wp_filesystem;
    
        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $wp_filesystem->put_contents($path, $content, FS_CHMOD_FILE);
    }
    
} 