<?php 

namespace MW_STATIC\Admin;

use MW_STATIC\Inc\Traits\License_Trait;

class Action_Buttons{
    use License_Trait;
    public function __construct(){
        if($this->is_active_license()){
            $this->load_hooks();
        }
     }

     private function load_hooks(){
        add_filter('post_row_actions', [$this, 'add_export_view_action'], 10, 2);
        add_filter('page_row_actions', [$this, 'add_export_view_action'], 10, 2);
     }

    public function add_export_view_action($actions, $post) {
        if (in_array($post->post_type, ['post', 'page'])) {
            $export_url = add_query_arg(
                array(
                    'action' => 'export_html_view',
                    'post_id' => $post->ID,
                ),
                admin_url('admin-ajax.php')
            );
            
            $actions['export_view'] = '<a href="#" class="export-view-link" data-post-id="' . $post->ID . '">' . __('Export As HTML', 'wp-to-static') . '</a>';
        }
        return $actions;
    }
}