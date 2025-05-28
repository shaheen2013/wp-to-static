<?php 

namespace MW_STATIC\Inc\Services\Downloader;

interface Download_Resource_Interface{
    public function downloadResource($html_content, $save_dir, $assets_url, $base_url, $urls_array, $style_file_title = "");
}