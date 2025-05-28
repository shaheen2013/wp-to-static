<?php
namespace MW_STATIC\Inc\Services\Fonts;

use MW_STATIC\Inc\Services\Downloader\Background_Image_Downloader;
use MW_STATIC\Inc\Services\Downloader\Downloader_Interface;
use MW_STATIC\Inc\Services\File\Create_Directory;
use MW_STATIC\Inc\Services\File\File_Saver;
use MW_STATIC\Inc\Services\File\Url_Handler;

class Font_Handler implements Font_Handler_Interface {

    private $supported_fonts = ['woff', 'woff2'];
    public function __construct(private Downloader_Interface $downloader, private Background_Image_Downloader $backgroundImage) {}

    public function download_fonts_from_styles($dom, $save_dir, $assets_url, $base_url, $style_file_title = "styles.css") {
        $this->remove_wp_emoji_script($dom);
        
        $all_css = '';
        $style_tags_to_remove = [];

        $style_tags = $dom->getElementsByTagName('style');
        foreach ($style_tags as $style_tag) {
            $css_content = $style_tag->nodeValue;
            $updated_css = $this->download_fonts_from_css($css_content, $save_dir, $assets_url, $base_url);
            $updated_css = $this->backgroundImage->download_background_images_from_css($updated_css, $save_dir, $assets_url, $base_url);
            $style_tag->nodeValue = $updated_css;
            $all_css .= $updated_css . "\n";
            $style_tags_to_remove[] = $style_tag;
        }

        foreach ($style_tags_to_remove as $style_tag) {
            $style_tag->parentNode->removeChild($style_tag);
        }

        $link_tags = $dom->getElementsByTagName('link');
        foreach ($link_tags as $link_tag) {
            if ($link_tag->getAttribute('rel') === 'stylesheet') {
                $css_url = $link_tag->getAttribute('href');
                $css_url = Url_Handler::resolve_url($base_url, $css_url);

                try {
                    $css_content = file_get_contents($css_url);
                    $updated_css = $this->download_fonts_from_css($css_content, $save_dir, $assets_url, $base_url);
                    $updated_css = $this->backgroundImage->download_background_images_from_css($updated_css, $save_dir, $assets_url, $base_url);
                    $all_css .= $updated_css . "\n";
                    $link_tag->setAttribute('href', "$assets_url/css/$style_file_title");
                } catch (\Exception $e) {
                    error_log('Error downloading CSS: ' . $e->getMessage());
                }
            }
        }

        $elements = $dom->getElementsByTagName('*');
        foreach ($elements as $element) {
            if ($element->hasAttribute('style')) {
                $inline_css = $element->getAttribute('style');
                $updated_inline_css = $this->download_fonts_from_inline_css($inline_css, $save_dir, $assets_url, $base_url);
                $updated_inline_css = $this->backgroundImage->download_background_images_from_inline_css($updated_inline_css, $save_dir, $assets_url, $base_url);
                //$all_css .= $updated_inline_css . "\n";
                $element->setAttribute('style', $updated_inline_css);
                //$element->removeAttribute('style');
            }
        }



        $css_file_path = $save_dir . '/css/styles';
        if(!empty($style_file_title)){
            $css_file_path = "$save_dir/css/$style_file_title";
        }
        Create_Directory::create(dirname($css_file_path));

        File_Saver::save($all_css, $css_file_path);
    }

    private function download_fonts_from_css($css_content, $save_dir, $assets_url, $base_url) {
        preg_match_all('/url\((["\']?)(.*?)\1\)/i', $css_content, $matches);
        if (!empty($matches[2])) {
            foreach ($matches[2] as $font_url) {
                $font_url = Url_Handler::resolve_url($base_url, $font_url);
                $font_name = basename(wp_parse_url($font_url, PHP_URL_PATH));
                $file_path = $save_dir . '/fonts/' . $font_name;
    
                $font_extension = pathinfo($font_name, PATHINFO_EXTENSION);
                
                if (in_array(strtolower($font_extension), $this->supported_fonts)) {
                    if ($this->downloader->download($font_url, $file_path)) {
                        $css_content = str_replace($font_url, "../fonts/$font_name", $css_content);
                    }
                }
            }
        }
    
        return $css_content;
    }
    
    private function download_fonts_from_inline_css($inline_css, $save_dir, $assets_url, $base_url) {
        preg_match_all('/url\((["\']?)(.*?)\1\)/i', $inline_css, $matches);
        if (!empty($matches[2])) {
            foreach ($matches[2] as $font_url) {
                $font_url = Url_Handler::resolve_url($base_url, $font_url);
                $font_name = basename(wp_parse_url($font_url, PHP_URL_PATH));
                $file_path = $save_dir . '/fonts/' . $font_name;
    
                $font_extension = pathinfo($font_name, PATHINFO_EXTENSION);
                
                if (in_array(strtolower($font_extension), $this->supported_fonts)) {
                    if ($this->downloader->download($font_url, $file_path)) {
                        $inline_css = str_replace($font_url, "../fonts/$font_name", $inline_css);
                    }
                }
            }
        }
    
        return $inline_css;
    }
    

    private function remove_wp_emoji_script($dom) {
        $script_tags = $dom->getElementsByTagName('script');
        $scripts_to_remove = [];
    
        foreach ($script_tags as $script) {
            $src = $script->getAttribute('src');
            if (strpos($src, 'wp-emoji-release.min.js') !== false) {
                $scripts_to_remove[] = $script;
            }
        }
    
        foreach ($scripts_to_remove as $script) {
            $script->parentNode->removeChild($script);
        }
    }
}
