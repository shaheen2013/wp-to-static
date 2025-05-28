<?php 

namespace MW_STATIC\Inc\Helper;

class Front_Page_Checker {
    public static function check($page_id) {
        $front_page_id = get_option('page_on_front');
        return $page_id == $front_page_id;
    }
    
}