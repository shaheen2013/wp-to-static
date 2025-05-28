<?php
namespace MW_STATIC\Inc\Services\Downloader;

use MW_STATIC\Inc\Helper\Urls_Replacer;
use MW_STATIC\Inc\Services\File\Url_Handler;

class Background_Image_Downloader {

    private $supported_images = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

    public function __construct(private Downloader_Interface $downloader) {}

    public function download_background_images_from_css($css_content, $save_dir, $assest_url, $base_url) {
        preg_match_all('/url\((["\']?)(.*?)\1\)/i', $css_content, $matches);
        preg_match_all('/--([a-zA-Z0-9\-]+)\s*:\s*URL\((["\']?)(.*?)\2\)/i', $css_content, $var_matches);
        $image_urls = array_merge($matches[2], $var_matches[3]);
        $url_array = [];
        if (!empty($image_urls)) {
            foreach ($image_urls as $image_url) {
                $image_url = Url_Handler::resolve_url($base_url, $image_url);
                $image_name = basename(wp_parse_url($image_url, PHP_URL_PATH));
                $file_path = $save_dir . '/images/' . $image_name;
                $new_image_url = str_replace(['https://', 'http://'], '//', $image_url);

                $image_extension = pathinfo($image_name, PATHINFO_EXTENSION);

                if (in_array(strtolower($image_extension), $this->supported_images)) {
                    $linked_img_url = "../images/$image_name";
                    $url_array[$image_url] = $linked_img_url;
                    $url_array[$new_image_url] = $linked_img_url;
                    if ($this->downloader->download($image_url, $file_path)) {
                        $css_content = str_replace([$image_url, $new_image_url], $linked_img_url, $css_content);
                    }
                }
            }
        }
        $css_content = Urls_Replacer::replace($css_content, $url_array);
        return $css_content;
    }
    
    

    public function download_background_images_from_inline_css($inline_css, $save_dir, $assest_url, $base_url) {
        return $this->download_background_images_from_css($inline_css, $save_dir, $assest_url, $base_url);
    }
}

