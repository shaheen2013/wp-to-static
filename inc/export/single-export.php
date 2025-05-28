<?php
namespace MW_STATIC\Inc\Export;

class Single_Export extends Base_Export {
    protected function load_hooks() {
        add_action('wp_ajax_mw_export_single', [$this, 'mw_export']);
    }
}