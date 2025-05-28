<?php
namespace MW_STATIC\Inc\Services;

use MW_STATIC\Inc\Services\Container;
use MW_STATIC\Inc\Services\Response\Response_Handler;
use MW_STATIC\Inc\Services\Response\Response_Handler_Interface;
use MW_STATIC\Inc\Services\Downloader\Download_Resource_Interface;
use MW_STATIC\Inc\Services\Downloader\Download_Resources;
use MW_STATIC\Inc\Services\Downloader\Downloader;
use MW_STATIC\Inc\Services\Downloader\Downloader_Interface;
use MW_STATIC\Inc\Services\Exporter\Export;
use MW_STATIC\Inc\Services\Exporter\Export_Interface;
use MW_STATIC\Inc\Services\Fonts\Font_Handler;
use MW_STATIC\Inc\Services\Fonts\Font_Handler_Interface;
use MW_STATIC\Inc\Services\Zip\Zip_Creator;
use MW_STATIC\Inc\Services\Zip\Zip_Interface;

class Services extends Container {
    public function __construct() {
        $this->register();
    }

    private function register(){
        $this->bind(Response_Handler_Interface::class, Response_Handler::class);
        $this->bind(Downloader_Interface::class, Downloader::class);
        $this->bind(Download_Resource_Interface::class, Download_Resources::class);
        $this->bind(Zip_Interface::class, Zip_Creator::class);
        $this->bind(Export_Interface::class, Export::class);
        $this->bind(Font_Handler_Interface::class, Font_Handler::class);
    }

    public function get($abstract){
        return $this->resolve($abstract);
    }
}