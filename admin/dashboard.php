<?php

namespace MW_STATIC\Admin;

class Dashboard extends Admin_Menu
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add_menu()
    {
        add_menu_page(
            'WP To Static',
            'WP To Static',
            'manage_options',
            'mw-static-dashboard',
            [$this, 'render_page']
        );
    }

    public function render_page()
    {
        $is_active = get_option('mw_static_active_license', false);
        $static_email = get_option('mw_static_email', "");
        $static_key = get_option('mw_static_license_key', "");

        if (!$is_active) {
            $button_text = "Active";
            $form_class = "mw-active-license";
            $btn_class = "btn btn-outline-primary";
        }else{
            $button_text = "Deactivate";
            $form_class = "mw-deactive-license";
            $btn_class = "btn btn-outline-danger";
        }
    ?>
        <div class="wrap mt-5">
            <h3>Dashboard</h3>
            <hr>
            <?php if(!$is_active): ?>
                <div class="alert alert-danger">
                    <strong>Warning!</strong> Active your plugin before using WP To Static.
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    <strong>Congratulation!</strong> Your license is activated
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-lg-4">
                    <h3>Your License</h3>
                    <hr>
                    <form id="<?php echo $form_class; ?>" method="post">
                        <div class="row">
                            <div class="col mb-2">
                                <input type="email" class="form-control" placeholder="Your Email" value="<?php echo $static_email; ?>" name="user_email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-2">
                                <input type="password" class="form-control" placeholder="License Key" value="<?php echo $static_key; ?>" name="license_key" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col text-end">
                                <button type="submit" class="<?php echo $btn_class; ?>"><?php echo $button_text; ?></button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4">
                    <h3>Required Server Status</h3>
                    <hr>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <strong>PHP Version:</strong> 8.0.2
                        </li>
                        <li class="list-group-item">
                            <strong>WordPress Version:</strong> 6.2
                        </li>
                        <li class="list-group-item">
                            <strong>ZIP Enabled:</strong> Yes
                        </li>
                        <li class="list-group-item">
                            <strong>Memory Limit:</strong> 256M
                        </li>
                        <li class="list-group-item">
                            <strong>Max Execution Time:</strong> 120 seconds
                        </li>
                        <li class="list-group-item">
                            <strong>Upload Max Filesize:</strong> 64M
                        </li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <?php 
                        $php_version          = phpversion();
                        $wordpress_version    = get_bloginfo('version');
                        $zip_enabled          = class_exists('ZipArchive') ? 'Yes' : 'No';
                        $memory_limit         = ini_get('memory_limit');
                        $max_execution_time   = ini_get('max_execution_time') . ' seconds';
                        $upload_max_filesize  = ini_get('upload_max_filesize');
                    ?>
                    <h3>Current Server Status</h3>
                    <hr>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <strong>PHP Version:</strong> <?php echo esc_html($php_version); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>WordPress Version:</strong> <?php echo esc_html($wordpress_version); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>ZIP Enabled:</strong> <?php echo esc_html($zip_enabled); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Memory Limit:</strong> <?php echo esc_html($memory_limit); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Max Execution Time:</strong> <?php echo esc_html($max_execution_time); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Upload Max Filesize:</strong> <?php echo esc_html($upload_max_filesize); ?>
                        </li>
                    </ul>
                </div>
                
            </div>
        </div>
    <?php
    }
}
