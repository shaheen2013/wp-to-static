<?php 

namespace MW_STATIC\Inc\Services\Downloader;

class Downloader implements Downloader_Interface {

    public function download($url, $save_path) {
        $response = wp_remote_get($url);
    
        if (is_wp_error($response)) {
            return false;
        }
    
        $content = wp_remote_retrieve_body($response);
    
        if ($content !== false) {
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                WP_Filesystem();
            }
            if ($wp_filesystem->put_contents($save_path, $content, FS_CHMOD_FILE)) {
                return true;
            }
        }
    
        return false;
    }
    
    
}