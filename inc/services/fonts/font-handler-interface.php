<?php 

namespace MW_STATIC\Inc\Services\Fonts;

interface Font_Handler_Interface{
    public function download_fonts_from_styles($dom, $save_dir, $assets_url, $base_url, $style_file_title = "styles");
}