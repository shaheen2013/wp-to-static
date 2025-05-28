<?php

namespace MW_STATIC\Inc\Services\Exporter;

use Exception;
use MW_STATIC\Inc\Helper\Urls_Replacer;
use MW_STATIC\Inc\Services\Downloader\Background_Image;
use MW_STATIC\Inc\Services\Downloader\Download_Resource_Interface;
use MW_STATIC\Inc\Services\Exporter\Export_Interface;
use MW_STATIC\Inc\Services\File\Clear_Directory;
use MW_STATIC\Inc\Services\File\Create_Directory;
use MW_STATIC\Inc\Services\File\File_Saver;
use MW_STATIC\Inc\Services\Zip\Zip_Interface;

class Export implements Export_Interface {

    public function __construct(private Zip_Interface $zipFileCreator, private Download_Resource_Interface $downloaderResource) {}

    public function save_web_page($url, $html_content, $root_dir, $sub_dir, $urls_array, $style_file_title = "", $current = 1, $total = 1) {
    
        $assest_url = "assets";
        Create_Directory::create($root_dir);
        Create_Directory::create("$root_dir/$assest_url");
    
        $folders = ['images', 'css', 'js', 'fonts'];
        foreach ($folders as $folder) {
            $folder_path = "$root_dir/$assest_url/$folder";
            Create_Directory::create($folder_path);
        }

        if($root_dir != $sub_dir){
            Create_Directory::create($sub_dir);
            $assest_url = "../$assest_url";

        }else{
            $sub_dir = $root_dir;
        }
        
        $assets_dir = "$root_dir/assets";
    
        $updated_html_content = $this->downloaderResource->downloadResource(
            $html_content, 
            $assets_dir,  
            $assest_url, 
            $url, 
            $urls_array, 
        $style_file_title);

        $html_path = "$sub_dir/index.html";

        if(count($urls_array) > 0){
            $updated_html_content = Urls_Replacer::replace($updated_html_content, $urls_array);
        }

        File_Saver::save($updated_html_content, $html_path);
    
        if ($current < $total) {
            return [
                'success' => true,
                "file" => ""
            ];
        }
    
        $zipFilePath = $this->zipFileCreator->createZip($root_dir, $root_dir . '.zip');
        Clear_Directory::delete_directory($root_dir);
        
        return [
            'success' => true,
            "file" => $zipFilePath
        ];
    }
    
    
}
