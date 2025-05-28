<?php
namespace MW_STATIC\Inc;

use MW_STATIC\Admin\Admin_Init;
use MW_STATIC\Inc\Services\File\Clear_Directory;
use MW_STATIC\Inc\Services\File\Create_Directory;
use MW_STATIC\Inc\Services\Services;

class Static_Init {

    private static  $mw_static_dir = MW_STATIC_UPLOAD_DIR;

    public function __construct() {
        $service = new Services();
       
        $service->get(Admin_Init::class);
        $service->get(Assets::class);
    }

    private static function create_mw_static_folder() {
        Create_Directory::create(self::$mw_static_dir);
    }
    
    private static function delete_mw_static_folder() {
        Clear_Directory::delete_directory(self::$mw_static_dir);
    }

    
    public static function activate() {

        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
        self::create_mw_static_folder();
        add_option( 'mw_static_activated', true );

    }

    public static function deactivate() {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
        
    }

    public static function uninstall() {
        self::delete_mw_static_folder();
        delete_option( 'mw_static_activated' );
        
    }
}
