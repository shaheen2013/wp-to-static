<?php 

namespace MW_STATIC\Inc;

class Assets{

    public function __construct() {
        $this->load_hooks(); 
    }

    private function load_hooks(){
        add_action('admin_enqueue_scripts', [$this, 'load_styles']);
        add_action('admin_enqueue_scripts', [$this, 'load_scripts']);
    }

    public function load_styles($hook){
        if(strpos( $hook, 'mw-' ) == false && $hook != 'edit.php') return;
        wp_enqueue_style(
            'mw-bootstrap',  
            MW_STATIC_PATH_URL . 'assets/css/bootstrap.min.css',
            [],
            '5.3.3',
            'all'
        );
        wp_enqueue_style(
            'mw-dataTables-bootstrap',
            MW_STATIC_PATH_URL . 'assets/css/dataTables.bootstrap5.css',
            [],
            "5.3.3",
            'all'
        );
        wp_enqueue_style(
            'mw-static-style', 
            MW_STATIC_PATH_URL . 'assets/css/style.css',
            [],
            wp_rand(),
            'all'
        );
    }


    public function load_scripts($hook) {
        
        if(strpos( $hook, 'mw-' ) == false && $hook != 'edit.php') return;

        wp_enqueue_script(
            'mw-bootstrap',
            MW_STATIC_PATH_URL . 'assets/js/bootstrap.bundle.min.js',
            ['jquery'],
            "5.3.3",
            true
        );
        wp_enqueue_script(
            'mw-dataTables',
            MW_STATIC_PATH_URL . 'assets/js/dataTables.js',
            ['jquery'],
            "2.1.8",
            true
        );
        wp_enqueue_script(
            'mw-dataTables-bootstrap',
            MW_STATIC_PATH_URL . 'assets/js/dataTables.bootstrap5.js',
            ['jquery'],
            "5.3.3",
            true
        );

        wp_enqueue_script(
            'mw-static-script',
            MW_STATIC_PATH_URL . 'assets/js/script.js',
            ['jquery'],
            wp_rand(), 
            true
        );

        $localized_data = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('mw_static_nonce'),
            'plugin_url' => MW_STATIC_PATH_URL
        ];

        wp_localize_script('mw-static-script', 'mwStatic', $localized_data);
    }
}