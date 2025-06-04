<?php
namespace MW_STATIC\Inc\Traits;

use MW_STATIC\Inc\Services\Repo\Plugin_Uri;

trait Http_Post_Trait {
    /**
     * Perform a POST request.
     *
     * @param string $url The request URL.
     * @param array $args Optional. Additional arguments for the request (e.g., headers, body).
     * @return array|WP_Error The response or WP_Error on failure.
     */
    public function http_post($endpoint, $data = [],  $header = []) {

        $uri = Plugin_Uri::get_plugin_uri();

        $base_url = "$uri/wp-json/wp-static/v1/";
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . get_option('mw_static_license_key', ""),
            'Email' => get_option('mw_static_email', ""),
            'Domain' => str_replace("www.", "", wp_unslash($_SERVER['HTTP_HOST']))
        ];
        if(!empty($header)){
            $headers = $header;
        }

        $args = [
            'timeout'   => 10,
            'headers'   => $headers,
            'body'      => wp_json_encode($data),
            'sslverify' => true,
        ];
        
        return wp_remote_post("{$base_url}$endpoint", $args);
    }

    /**
     * Check if a POST request succeeded.
     *
     * @param array|WP_Error $response The response or WP_Error.
     * @return bool True if the request was successful, false otherwise.
     */
    private function is_post_request_successful($response) {
        if (is_wp_error($response)) {
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);

        return $status_code >= 200 && $status_code < 300;
    }

    /**
     * Retrieve the body of a successful POST request.
     *
     * @param array|WP_Error $response The response or WP_Error.
     * @return string|null The response body or null on failure.
     */
    public function get_response_body($response) {
        if ($this->is_post_request_successful($response)) {
            $response_body =  wp_remote_retrieve_body($response);
            return json_decode($response_body, true);
        }

        return null;
    }
}
