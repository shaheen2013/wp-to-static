<?php
namespace MW_STATIC\Inc\Export;

class Multi_Export extends Base_Export {
    protected function load_hooks() {
        add_action('wp_ajax_mw_export_multiple', [$this, 'mw_export']);
    }
}