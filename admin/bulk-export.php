<?php

namespace MW_STATIC\Admin;

use MW_STATIC\Inc\Services\Repo\Content_Repo;
use MW_STATIC\Inc\Traits\License_Trait;

class Bulk_Export extends Admin_Menu
{
    use License_Trait;

    public function __construct(private Content_Repo $contents)
    {
        if ($this->is_active_license()) {
            parent::__construct();
        }
    }

    public function add_menu()
    {
        add_submenu_page(
            'mw-static-dashboard',
            __('Bulk Export', 'wp-to-static'),
            __('Bulk Export', 'wp-to-static'),
            'manage_options',
            'mw-static-export',
            [$this, 'render_page']
        );
    }

    public function render_page()
{
    $postType = isset($_GET['postType']) ? sanitize_text_field(wp_unslash($_GET['postType'])) : '';
    $postAuthor = isset($_GET['postAuthor']) ? sanitize_text_field(wp_unslash($_GET['postAuthor'])) : '';
    $categoryId = isset($_GET['category']) && $postType == "post" ? absint(wp_unslash($_GET['category'])) : '';
    $startDate = isset($_GET['startDate']) ? sanitize_text_field(wp_unslash($_GET['startDate'])) : '';
    $endDate = isset($_GET['endDate']) ? sanitize_text_field(wp_unslash($_GET['endDate'])) : '';

    $available_post_types = [
        'page' => "Page",
        'post' => 'Post'
    ];
    ?>
    <div class="wrap mt-4">
        <h3>
            <?php esc_html_e(get_admin_page_title(), 'wp-to-static'); ?> 
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#postFilter">
                <?php esc_html_e('Filter', 'wp-to-static'); ?>
            </button> 
            <a href="#" class="btn btn-outline-info" id="export-all"><?php esc_html_e('Export All', 'wp-to-static'); ?>  (<span class="total_exportable">0</span>)</a>
        </h3>
        <hr>

        <!-- The Modal -->
        <div class="modal" id="postFilter">
            <div class="modal-dialog">
                <form id="post-filter" method="GET" action="">
                    <input type="hidden" name="page" value="mw-static-export">
                    
                    <!-- Add nonce field for security -->
                    <?php wp_nonce_field('mw_static_export_filter_action', 'mw_static_export_nonce'); ?>

                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title"><?php esc_html_e('Filter Content', 'wp-to-static'); ?></h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <!-- Post Type -->
                                <div class="col-md-6">
                                    <label for="postType" class="form-label"><?php esc_html_e('Post Type', 'wp-to-static'); ?></label>
                                    <select class="form-select" id="postType" name="postType">
                                        <option value="" selected><?php esc_html_e('All Types', 'wp-to-static'); ?></option>
                                        <?php if ($available_post_types): ?>
                                            <?php foreach ($available_post_types as $type => $val): ?>
                                                <option value="<?php echo esc_attr($type); ?>" <?php selected($type, $postType); ?>>
                                                    <?php echo esc_html($val); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <!-- Post Author -->
                                <div class="col-md-6">
                                    <label for="postAuthor" class="form-label"><?php esc_html_e('Post Author', 'wp-to-static'); ?></label>
                                    <select class="form-select" id="postAuthor" name="postAuthor">
                                        <option value="" selected><?php esc_html_e('All Authors', 'wp-to-static'); ?></option>
                                        <?php if ($authors = $this->contents->get_authors()): ?>
                                            <?php foreach ($authors as $author): ?>
                                                <option value="<?php echo esc_attr($author['id']); ?>" <?php selected($author['id'], $postAuthor) ?>>
                                                    <?php echo esc_html($author['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3" id="category-selector">
                                <!-- Category -->
                                <div class="col-md-12">
                                    <label for="category" class="form-label"><?php esc_html_e('Category', 'wp-to-static'); ?></label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="" selected><?php esc_html_e('All Categories', 'wp-to-static'); ?></option>
                                        <?php if ($categories = $this->contents->get_all_categories()): ?>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo esc_attr($category['id']); ?>" <?php selected($category['id'], $categoryId) ?>>
                                                    <?php echo esc_html($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <!-- Date Range -->
                                <div class="col-md-6">
                                    <label for="startDate" class="form-label"><?php esc_html_e('Start Date', 'wp-to-static'); ?></label>
                                    <input type="date" class="form-control" id="startDate" name="startDate" value="<?php echo esc_attr($startDate); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="endDate" class="form-label"><?php esc_html_e('End Date', 'wp-to-static'); ?></label>
                                    <input type="date" class="form-control" id="endDate" name="endDate" value="<?php echo esc_attr($endDate); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"><?php esc_html_e('Filter', 'wp-to-static'); ?></button>
                            <button type="reset" class="btn btn-secondary"><?php esc_html_e('Reset', 'wp-to-static'); ?></button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><?php esc_html_e('Close', 'wp-to-static'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- End Modal -->

        <!-- Table -->
        <table id="datatable" class="table table-striped table-bordered">
            <thead class="table-light">
                <tr>
                    <th width="50"><input type="checkbox" class="select-all"></th>
                    <th><?php esc_html_e('Title', 'wp-to-static'); ?></th>
                    <th><?php esc_html_e('Type', 'wp-to-static'); ?></th>
                    <th><?php esc_html_e('Author', 'wp-to-static'); ?></th>
                    <th><?php esc_html_e('Category', 'wp-to-static'); ?></th>
                    <th width="200"><?php esc_html_e('Date', 'wp-to-static'); ?></th>
                    <th><?php esc_html_e('Action', 'wp-to-static'); ?></th>
                </tr>
            </thead>
            <tfoot class="table-light">
                <tr>
                    <th><input type="checkbox" class="select-all"></th>
                    <th><?php esc_html_e('Title', 'wp-to-static'); ?></th>
                    <th><?php esc_html_e('Type', 'wp-to-static'); ?></th>
                    <th><?php esc_html_e('Author', 'wp-to-static'); ?></th>
                    <th><?php esc_html_e('Category', 'wp-to-static'); ?></th>
                    <th><?php esc_html_e('Date', 'wp-to-static'); ?></th>
                    <th><?php esc_html_e('Action', 'wp-to-static'); ?></th>
                </tr>
            </tfoot>
            <tbody>
                <?php if ($contents = $this->contents->get_all($postType, $postAuthor, $categoryId, $startDate, $endDate)): ?>
                    <?php foreach ($contents as $content): ?>
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="<?php echo esc_attr($content['id']); ?>" class="row-checkbox"></td>
                            <td><?php echo esc_html($content['title']); ?></td>
                            <td><?php echo esc_html($content['post_type']); ?></td>
                            <td><?php echo esc_html($content['author']); ?></td>
                            <td><?php echo esc_html($content['category']); ?></td>
                            <td><?php echo esc_html($content['publish_date']); ?></td>
                            <td>
                                <a href="<?php echo esc_url(get_permalink($content['id'])); ?>" target="_blank" class="view text-decoration-none"><?php esc_html_e('View', 'wp-to-static'); ?></a> | 
                                <a href="#" class="export-view-link text-decoration-none" data-post-id="<?php echo esc_attr($content['id']); ?>"><?php esc_html_e('Export', 'wp-to-static'); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php
}

}
