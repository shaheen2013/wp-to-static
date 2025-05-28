<?php 

namespace MW_STATIC\Inc\Services\File;

class Clear_Directory {

    public static function empty_directory($dir) {

        if (!is_dir($dir)) {
            return false;
        }
        
        global $wp_filesystem;
    
        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }
        $files = $wp_filesystem->dirlist($dir);
    
        if (!is_array($files)) {
            return false;
        }
    
        foreach ($files as $file => $file_info) {
            $file_path = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
    
            if ($file_info['type'] === 'd') { 
                self::empty_directory($file_path);
    
                if (!$wp_filesystem->rmdir($file_path)) {
                    return false;
                }
            } else { 
                if (!$wp_filesystem->delete($file_path)) {
                    return false;
                }
            }
        }
    
        return true;
    }
    

    public static function delete_directory($dir) {
        if (!is_dir($dir)) {
            return;
        }

        global $wp_filesystem;

        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $files = $wp_filesystem->dirlist($dir);

        foreach ($files as $file => $file_info) {
            $file_path = $dir . DIRECTORY_SEPARATOR . $file;
            if ($file_info['type'] == 'dir') {
                self::delete_directory($file_path);
            } else {
                $wp_filesystem->delete($file_path);
            }
        }

        $wp_filesystem->rmdir($dir);
    }
}
