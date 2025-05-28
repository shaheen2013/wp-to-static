<?php

namespace MW_STATIC\Inc;

abstract class Base_Ajax{

    public function __construct(){
        $this->load_hooks();
    }
    
    abstract protected function load_hooks();

    protected function validate_request() {
        if (!check_ajax_referer('mw_static_nonce', 'nonce', false) ||  empty($_POST['nonce'])) {
            return false;
        }
        return true;
    }
}