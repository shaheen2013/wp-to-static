<?php
namespace MW_STATIC\Inc\Traits;

use MW_STATIC\Inc\Services\Repo\Plugin_Uri;

trait Http_Get_Trait {
    /**
     * Perform a GET request.
     *
     * @param string $url The request URL.
     * @param array $args Optional. Additional arguments for the request (e.g., headers, query parameters).
     * @return array|WP_Error The response or WP_Error on failure.
     */
    public function http_get($endpoint, $args = []) {
        $uri = Plugin_Uri::get_plugin_uri();

        $base_url = "$uri/wp-json/wp-static/v1/";

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . get_option('mw_static_license_key', ""),
            'Email' => get_option('mw_static_email', ""),
            'Domain' => str_replace("www.", "", wp_unslash($_SERVER['HTTP_HOST']))
        ];

        $defaults = [
            'timeout'   => 10, // Set a reasonable timeout.
            'headers'   => $headers,
            'sslverify' => false,
        ];

        $args = wp_parse_args($args, $defaults);

        return wp_remote_get("{$base_url}$endpoint", $args);
    }

    /**
     * Check if a GET request succeeded.
     *
     * @param array|WP_Error $response The response or WP_Error.
     * @return bool True if the request was successful, false otherwise.
     */
    public function is_get_request_successful($response) {
        if (is_wp_error($response)) {
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);

        return $status_code >= 200 && $status_code < 300;
    }

    /**
     * Retrieve the body of a successful GET request.
     *
     * @param array|WP_Error $response The response or WP_Error.
     * @return string|null The response body or null on failure.
     */
    public function get_response_body($response) {
        if ($this->is_get_request_successful($response)) {
            $response_body = wp_remote_retrieve_body($response);
            return json_decode($response_body, true);
        }

        return null;
    }
}
