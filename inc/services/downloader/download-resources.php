<?php 
namespace MW_STATIC\Inc\Services\Downloader;

use DOMElement;
use DOMDocument;
use MW_STATIC\Inc\Services\File\File_Extensions;
use MW_STATIC\Inc\Services\File\Url_Handler;
use MW_STATIC\Inc\Services\Fonts\Font_Handler_Interface;

class Download_Resources implements Download_Resource_Interface {

    public function __construct(private Downloader_Interface $downloader, private Font_Handler_Interface $fontHandler) {}

    public function downloadResource($html_content, $save_dir, $assets_url, $base_url, $urls_array, $style_file_title = "") {
        $dom = new DOMDocument();
        @$dom->loadHTML($html_content);

        $tags_to_download = [
            'img' => ['attr' => 'src', 'folder' => 'images'],
            'link' => ['attr' => 'href', 'folder' => 'css'],
            'script' => ['attr' => 'src', 'folder' => 'js'],
            'font' => ['attr' => 'href', 'folder' => 'fonts']
        ];

        $ignore_extensions = ['php', 'json', 'xml'];

        $this->fontHandler->download_fonts_from_styles($dom, $save_dir, $assets_url, $base_url, $style_file_title);

        foreach ($tags_to_download as $tag => $config) {
            $elements = $dom->getElementsByTagName($tag);
            foreach ($elements as $element) {
                if ($element instanceof DOMElement && $element->hasAttribute($config['attr'])) {
                    $url_attr = $element->getAttribute($config['attr']);
                    if (!$url_attr) {
                        continue;
                    }
                    

                    $resource_url = Url_Handler::resolve_url($base_url, $url_attr);

                    if (strpos($resource_url, 'https://fonts.googleapis.com') === 0) {
                        continue;
                    }

                    if (strpos($resource_url, '?') !== false) {
                        $url_parts = wp_parse_url($resource_url);
                        if (isset($url_parts['query'])) {
                            $file_name = File_Extensions::get_file_name_with_extension($url_parts['path']) . '?' . $url_parts['query'];
                        } else {
                            $file_name = File_Extensions::get_file_name_with_extension($resource_url);
                        }
                    } else {
                        $file_name = File_Extensions::get_file_name_with_extension($resource_url);
                    }
 
                    $file_extension = File_Extensions::get_file_extension($file_name, $resource_url);

                    if (in_array(strtolower($file_extension), $ignore_extensions)) {
                        continue;
                    }
                    $file_name = File_Extensions::remove_double_extension($file_name, $file_extension);

                    $folder = File_Extensions::get_folder_for_extension($file_extension);

                    if ($folder) {
                        $file_path = "$save_dir/$folder/$file_name";

                        if ($this->downloader->download($resource_url, $file_path)) {
                            $relative_path = "$assets_url/$folder/$file_name";
                            $element->setAttribute($config['attr'], $relative_path);

                            if ($tag === 'img') {
                                $this->processImageElement($element, $file_name, $folder);
                            }
                        }
                    }
                }
            }
        }

        if(!empty($urls_array)){
            $this->update_hrefs($dom, $urls_array);
        }

        libxml_clear_errors();

        return $dom->saveHTML();
    }

    private function processImageElement(DOMElement $element, string $secure_name, string $folder) {
        if ($element->hasAttribute('srcset')) {
            $srcset = $element->getAttribute('srcset');
            $updated_srcset = $this->updateSrcset($srcset, $folder, $secure_name);
            $element->setAttribute('srcset', $updated_srcset);
        }

        if ($element->hasAttribute('data-id')) {
            $element->setAttribute('data-id', '***');
        }
        if ($element->hasAttribute('data-link')) {
            $element->setAttribute('data-link', '#');
        }
    }

    private function updateSrcset(string $srcset, string $folder, string $secure_name): string {
        $lines = explode(',', $srcset);
        $updated_lines = [];

        foreach ($lines as $line) {
            $parts = preg_split('/\s+/', trim($line));
            if (!empty($parts)) {
                $updated_lines[] = $folder . '/' . $secure_name . (isset($parts[1]) ? ' ' . $parts[1] : '');
            }
        }

        return implode(', ', $updated_lines);
    }

    private function update_hrefs(DOMDocument $dom, $href_map) {
        $elements = $dom->getElementsByTagName('a');
        foreach ($elements as $element) {
            if ($element instanceof DOMElement && $element->hasAttribute('href')) {
                $current_href = $element->getAttribute('href');
                if (array_key_exists($current_href, $href_map)) {
                    $element->setAttribute('href', $href_map[$current_href]);
                }
            }
        }
    }
}
