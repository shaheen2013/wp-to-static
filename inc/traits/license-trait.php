<?php 

namespace MW_STATIC\Inc\Traits;

trait License_Trait {
    use Http_Get_Trait;
    protected function is_active_license() {
        $license_key = get_option('mw_static_license_key', "");

        if($license_key == ""){
            return false;
        }

        $api_res = $this->http_get("license-validator");

        $response_body = $this->get_response_body($api_res);

        if(! $response_body || $response_body['success'] == false){
            update_option('mw_static_license_key', "");
            update_option('mw_static_email', "");
            update_option('mw_static_active_license', false);
            return false;
        }

        return $response_body['success'] == true;
    }
}