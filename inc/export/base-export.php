<?php
namespace MW_STATIC\Inc\Export;

use Exception;
use MW_STATIC\Inc\Helper\Extract_Domain_Name;
use MW_STATIC\Inc\Helper\Extract_Title;
use MW_STATIC\Inc\Helper\Front_Page_Checker;
use MW_STATIC\Inc\Services\Exporter\Export_Interface;
use MW_STATIC\Inc\Services\File\Clear_Directory;
use MW_STATIC\Inc\Services\Response\Response_Handler_Interface;
use MW_STATIC\Inc\Traits\Http_Post_Trait;

abstract class Base_Export {
    use Http_Post_Trait;
    protected $uploadDir;

    private $url;

    private $total_contents;

    private $current_content;

    private $post_id;

    private $domain_name;

    private $urls_array;

    public function __construct(private Response_Handler_Interface $responseHandler, private Export_Interface $export) {
        $this->load_hooks();
        $this->uploadDir = MW_STATIC_UPLOAD_DIR;
    }

    abstract protected function load_hooks();
    public function mw_export() {
        if (!$this->validate_request())  return;

        $this->assign_data($_POST);

        if($this->current_content === 1) { 
            $this->clear_directory();
        }

        $api_response = $this->http_post('exporter', ["Url" => $this->url]);

        $res_body = $this->get_response_body($api_response);

        if(empty($res_body['data']) || $res_body['data'] == null) {
            $this->responseHandler->send_error_response('Failed to retrieve data from the API', [
                'download_url' => "",
            ]);
            return;
        }

        try {
            

            $saved_directory = $this->get_save_directory($this->domain_name );
            $sub_directory = $this->get_sub_folder_name();
            $style_file_title = $this->get_style_file_name();
           
            $html_content = base64_decode($res_body['data']);

            $res = $this->export->save_web_page($this->url, $html_content, $saved_directory, $sub_directory, $this->urls_array, $style_file_title, $this->current_content, $this->total_contents);

            $download_url = $this->get_download_url($res, $this->current_content, $this->total_contents);
            
            $this->responseHandler->send_success_response('Web page exported successfully', [
                'download_url' => $download_url,
            ]);

        } catch (Exception $e) {
            $this->responseHandler->send_error_response('An error occurred: ' . $e->getMessage());
        }
    }

    protected function validate_request() {
        if (!check_ajax_referer('mw_static_nonce', 'nonce', false) || !isset($_POST['post_id']) || empty($_POST['nonce'])) {
            $this->responseHandler->send_error_response('Invalid Request');
            return false;
        }
        return true;
    }

    protected function clear_directory() {
        Clear_Directory::empty_directory($this->uploadDir);
    }

    protected function get_save_directory($folder_name) {
        return $this->uploadDir . '/' . $folder_name;
    }

    protected function get_download_url($res, $current, $total) {
        $download_url = '';
        if (!empty($res['file']) && ($current === $total)) {
            $relative_path = str_replace(WP_CONTENT_DIR, '', $res['file']);
            $download_url = content_url($relative_path);
        }
        return $download_url;
    }

    private function get_sub_folder_name() {
        
        if(!Front_Page_Checker::check($this->post_id)){
            $this->domain_name = $this->domain_name . '/' . Extract_Title::extract($this->url);
        }else{
            unset($this->urls_array[$this->url]);
        }

        return $this->get_save_directory($this->domain_name );
    }

    private function get_style_file_name(){
        if($this->total_contents == 1){
            $style_file_title = 'style.css';
        }else{
            $style_file_title = Extract_Title::extract($this->url) . '.css';
        }

        return $style_file_title;
    }

    private function assign_data($data){
        $this->current_content = intval($data['current']);
        $this->total_contents = intval($data['total']);

        $this->post_id = intval($data['post_id']);

        $this->url = get_permalink($this->post_id);

        $this->urls_array = $this->extract_url($data['post_ids']);

        $this->domain_name = Extract_Domain_Name::extract($this->url);
    }

    private function extract_url($ids){
        $urls = [];
        foreach ($ids as $id) {
            $url = get_permalink($id);
            $title = Extract_Title::extract($url);
            $urls[$url] = "/$title";
        }
        return $urls;
    }
}