<?php 
namespace MW_STATIC\Inc\License;

use MW_STATIC\Inc\Base_Ajax;
use MW_STATIC\Inc\Services\Response\Response_Handler_Interface;
use MW_STATIC\Inc\Traits\Http_Post_Trait;

class Active_License extends Base_Ajax{

    use Http_Post_Trait;
    public function __construct(private Response_Handler_Interface $responseHandler) {
        parent::__construct();
        $this->load_hooks();
    }

    protected function load_hooks(){
        add_action('wp_ajax_active_license', [$this, 'active_license']);
    }

    public function active_license(){
        if (!$this->validate_request() 
            || !isset($_POST['user_email']) 
            || empty($_POST['user_email']) 
            || !isset($_POST['license_key']) 
            || empty($_POST['license_key'])){
            $this->responseHandler->send_error_response('Invalid request');
            return;
        }
        $license_data = array_map('sanitize_text_field', $_POST);
        $license_data['user_email'] = sanitize_email($license_data['user_email']);

        extract($license_data);

        try{

            $args = array(
                'Email' => $user_email,
                'License_Key' => $license_key
            );

            $header = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $license_key,
                'Domain' => wp_unslash($_SERVER['HTTP_HOST'])
            ];

            $url = "activate-domain";

            $response = $this->http_post($url, $args, $header);
            $response_body = $this->get_response_body($response);

            if($response_body && $response_body['success']){
                update_option('mw_static_active_license', true);
                update_option('mw_static_email', $user_email);
                update_option('mw_static_license_key', $license_key);
                $this->responseHandler->send_success_response($response_body['message']);
            }else{
                $this->responseHandler->send_error_response('Failed to activate license');
            }

        }catch(\Exception $e){
            $this->responseHandler->send_error_response('Error: ' . $e->getMessage());
        }

    }
}
