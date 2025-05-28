<?php 

namespace MW_STATIC\Inc\Services\File;

class File_Extensions{
    public static function get_folder_for_extension($file_extension) {
        $extension_map = [
            'jpg'  => 'images',
            'jpeg' => 'images',
            'png'  => 'images',
            'gif'  => 'images',
            'webp' => 'images',
            'svg'  => 'images',
            'css'  => 'css',
            'js'   => 'js',
            'woff' => 'fonts',
            'woff2'=> 'fonts',
            'ttf'  => 'fonts',
            'otf'  => 'fonts',
            'eot'  => 'fonts'
        ];
        return $extension_map[strtolower($file_extension)] ?? null;
    }

    public static function get_file_name_with_extension($url) {
        $file_name = basename(wp_parse_url($url, PHP_URL_PATH));
        $file_extension = self::get_file_extension($file_name, $url);

        if (empty($file_extension)) {
            $file_extension = 'bin';
        }

        return $file_name . '.' . $file_extension;
    }

    public static function get_file_extension($file_name, $url) {
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
    
        if (empty($file_extension)) {
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $headers = @get_headers($url, 1);
                if ($headers !== false && isset($headers['Content-Type'])) {
                    $mime_type = is_array($headers['Content-Type']) ? $headers['Content-Type'][0] : $headers['Content-Type'];
                    $extension_map = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/gif' => 'gif',
                        'image/webp' => 'webp',
                        'image/svg+xml' => 'svg',
                        'text/css' => 'css',
                        'application/javascript' => 'js',
                        'text/javascript' => 'js',
                        'application/json' => 'json',
                        'text/html' => 'html',
                    ];
    
                    $file_extension = $extension_map[$mime_type] ?? 'bin';
                } else {
                    $file_extension = 'bin';
                }
            } else {
                $file_extension = 'bin';
            }
        }
        return $file_extension;
    }

    public static function remove_double_extension($file_name, $file_extension) {
        if (substr($file_name, -strlen($file_extension) - 1) === '.' . $file_extension) {
            $file_name = substr($file_name, 0, -strlen($file_extension) - 1);
        }

        return $file_name;
    }
}