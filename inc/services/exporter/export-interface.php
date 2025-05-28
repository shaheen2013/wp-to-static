<?php 

namespace MW_STATIC\Inc\Services\Exporter;

interface Export_Interface{
    public function save_web_page($url, $html_content, $root_dir, $sub_dir, $urls_array, $style_file_title = "", $current = 1, $total = 1);
}