<?php
namespace MW_STATIC\Inc\Services\Repo;

class Content_Repo {

    /**
     * Get all posts and pages
     *
     * @return array List of posts and pages
     */
    public static function get_all($post_type = "", $postAuthor = "", $category = "", $startDate = "", $endDate = "") {
        $args = [
            'post_type' => !empty($post_type) ? (array)$post_type : ['post', 'page']
        ];
        if (!empty($postAuthor)) {
            $args['author'] = (int)$postAuthor;
        }
    
        if (!empty($category)) {
            $args['cat'] = (int)$category; 
        }
    
        if (!empty($startDate) || !empty($endDate)) {
            $date_query = [];
            if (!empty($startDate)) {
                $date_query['after'] = $startDate;
            }
            if (!empty($endDate)) {
                $date_query['before'] = $endDate;
            }
            $args['date_query'] = [$date_query];
        }
    
        return self::query_posts($args);
    }
    

    /**
     * Get only posts
     *
     * @return array List of posts
     */
    public static function get_posts() {
        return self::query_posts(['post_type' => 'post']);
    }

    /**
     * Get only pages
     *
     * @return array List of pages
     */
    public static function get_pages() {
        return self::query_posts(['post_type' => 'page']);
    }

    /**
     * Get all categories
     *
     * @return array List of categories with ID and name
     */
    public static function get_categories() {
        $categories = get_categories();
        $result = [];
        
        foreach ($categories as $category) {
            $result[] = [
                'id' => $category->term_id,
                'name' => $category->name
            ];
        }
        
        return $result;
    }

    /**
     * Get all authors
     *
     * @return array List of authors with ID and name
     */
    public static function get_authors() {
        $users = get_users(['role__in' => ['Author', 'Administrator', 'Editor']]);
        $result = [];

        foreach ($users as $user) {
            $result[] = [
                'id' => $user->ID,
                'name' => $user->display_name
            ];
        }

        return $result;
    }

    /**
     * Get all categories (id and name only)
     *
     * @return array List of categories
     */
    public static function get_all_categories() {
        $categories = get_categories(['hide_empty' => false]);
        $result = [];

        foreach ($categories as $category) {
            $result[] = [
                'id' => $category->term_id,
                'name' => $category->name
            ];
        }

        return $result;
    }

    /**
     * Query posts with specified arguments
     *
     * @param array $args Query arguments
     * @return array List of posts
     */
    private static function query_posts($args) {
        $query_args = array_merge([
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ], $args);

        $posts = get_posts($query_args);
        $result = [];

        foreach ($posts as $post) {
            $result[] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'author' => get_the_author_meta('display_name', $post->post_author),
                'publish_date' => $post->post_date,
                'update_date' => $post->post_modified,
                'category' => self::get_post_category($post->ID),
                'post_type' => $post->post_type
            ];
        }

        return $result;
    }

    /**
     * Get category name for a given post
     *
     * @param int $post_id Post ID
     * @return string|null Category name or null if no category
     */
    private static function get_post_category($post_id) {
        $categories = get_the_category($post_id);

        if (!empty($categories) && is_array($categories)) {
            return $categories[0]->name; // Return the first category
        }

        return null;
    }
}
