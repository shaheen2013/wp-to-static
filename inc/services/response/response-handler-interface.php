<?php 

namespace MW_STATIC\Inc\Services\Response;


interface Response_Handler_Interface {
    public function send_error_response(string $message, array $data = []): void;
    public function send_success_response(string $message, array $data = []): void;
}

