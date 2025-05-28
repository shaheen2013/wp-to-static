<?php

namespace MW_STATIC\Inc\Services\Response;

class Response_Handler implements Response_Handler_Interface{
    
    public function send_error_response(string $message, array $data = []): void {
        wp_send_json_error(array('message' => $message, 'data' => $data));
    }

    public function send_success_response(string $message, array $data = []): void{
        wp_send_json_success(array('message' => $message, 'data' => $data));
    }
}