<?php 
namespace MW_STATIC\Admin;

abstract class Admin_Menu{
    

    public function __construct(){
        
        $this->load_hooks();
    }

    private function load_hooks(){
        add_action('admin_menu', [$this, 'add_menu']);
    }

    abstract public function add_menu();

    abstract public function render_page();
}