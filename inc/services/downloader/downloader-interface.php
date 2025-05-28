<?php 

namespace MW_STATIC\Inc\Services\Downloader;

interface Downloader_Interface {
    public function download($url, $save_path);
}