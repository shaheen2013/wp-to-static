<?php
namespace MW_STATIC\Inc\Services\Repo;

class Plugin_Uri{

    public static function get_plugin_uri() {
        $plugin_file = MW_STATIC_DIR_PATH . '/index.php';
        $plugin_headers = [
            'AccessUrl' => 'Access Url',
        ];

        $plugin_data = get_file_data($plugin_file, $plugin_headers);

        if (!empty($plugin_data['AccessUrl'])) {
           return $plugin_data['AccessUrl'];
        } else {
            return null;
        }
    }
}