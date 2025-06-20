<?php
/*
Plugin Name: Easy StagePush Receiver
Description: Sync posts with ACF fields and media from a dev environment via REST.
Version: 1.0
Requires at least: 6.3
Requires PHP: 7.2.24
Author: Anandhu Nadesh
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Text Domain: easy-stagepush-receiver
*/

add_action('rest_api_init', function () {
    register_rest_route('esps-sync/v1', '/import-post', [
        'methods' => 'POST',
        'callback' => 'espr_import_post_from_dev',
        'permission_callback' => function () {
            return true;
        }
    ]);
});

function espr_import_post_from_dev($request)
{
    error_log('starting import!');
    $params = $request->get_json_params();
    $post_data = $params['post'];
    $acf_data = $params['acf_fields'];
    $featured_image_url = $post_data['featured_image_url'] ?? null;
    $page_template = $post_data['_wp_page_template'] ?? null;
    $page_path = $post_data['path'] ?? null;
    unset($post_data['featured_image_url']);
    unset($post_data['_wp_page_template']);
    unset($post_data['path']);

    if (!empty($post_data['parent_lookup'])) {
        $parent_path = $post_data['parent_lookup']['path'];
        $parent_type = $post_data['parent_lookup']['post_type'];

        $parent = get_page_by_path($parent_path, OBJECT, $parent_type);
        if ($parent) {
            $post_data['post_parent'] = $parent->ID;
        }

        unset($post_data['parent_lookup']);
    }

    $existing = get_page_by_path($page_path, OBJECT, $post_data['post_type']);
    error_log('incoming post slug '.$post_data['post_name']);
    error_log('incoming post type '.$post_data['post_type']);
    error_log(print_r($existing, true));
    if ($existing) {
        error_log('post exists!!');
        $post_data['ID'] = $existing->ID;
    }

    if ($post_id = wp_insert_post($post_data)) {
        error_log('post inserted');
        error_log($post_id);
        if (is_wp_error($post_id)) {
            return new WP_REST_Response(['error' => 'Post creation failed'], 500);
        }

        if ($page_template)
            update_post_meta($post_id, '_wp_page_template', $page_template);

        if ($featured_image_url) {
            $existing_id = espr_find_existing_attachment_by_url($featured_image_url);
            if (!$existing_id) {
                $existing_id = espr_sideload_file($featured_image_url, $post_id);
            }
            if ($existing_id) {
                set_post_thumbnail($post_id, $existing_id);
            }
        }

        if (is_array($acf_data)) {
            $acf_data = espr_handle_acf_media($acf_data, $post_id);
            foreach ($acf_data as $key => $value) {
                update_field($key, $value, $post_id);
            }
        }

        $taxonomies = $params['taxonomies'] ?? [];
        foreach ($taxonomies as $taxonomy => $slugs) {
            $term_ids = [];
            foreach ($slugs as $slug) {
                $term = get_term_by('slug', $slug, $taxonomy);
                if (!$term) {
                    $term = wp_insert_term(str_replace('-', ' ', $slug), $taxonomy, ['slug' => $slug]);
                    if (is_wp_error($term)) continue;
                    $term_ids[] = $term['term_id'];
                } else {
                    $term_ids[] = $term->term_id;
                }
            }
            wp_set_object_terms($post_id, $term_ids, $taxonomy);
        }

        $yoast_meta = $params['yoast_meta'] ?? [];
        foreach ($yoast_meta as $meta_key => $meta_value) {
            update_post_meta($post_id, $meta_key, $meta_value);
        }
    }

    return new WP_REST_Response(['post_id' => $post_id], 200);
}

function espr_handle_acf_media($fields, $post_id)
{
    foreach ($fields as $key => &$value) {
        if (is_array($value)) {
            if (!empty($value) && isset($value[0]['post_type'], $value[0]['slug'])) {
                $value = array_map('espr_resolve_post_reference', $value);
            } elseif (isset($value['post_type'], $value['slug'])) {
                $resolved = espr_resolve_post_reference($value);
                $value = $resolved ?? null;
            } else {
                $value = espr_handle_acf_media($value, $post_id);
            }
        } elseif (is_string($value) && filter_var($value, FILTER_VALIDATE_URL) && strpos($value, '/uploads/') !== false) {
            $existing_id = espr_find_existing_attachment_by_url($value);
            if ($existing_id) {
                $value = $existing_id;
            } else {
                $value = espr_sideload_file($value, $post_id);
            }
        }
    }

    return $fields;
}

function espr_resolve_post_reference($ref) {
    if (is_array($ref) && isset($ref['post_type'], $ref['slug'])) {
        $post = get_page_by_path($ref['slug'], OBJECT, $ref['post_type']);
        return $post ? $post->ID : null;
    }
    return null;
}

function espr_find_existing_attachment_by_url($url)
{
    $filename = basename(parse_url($url, PHP_URL_PATH));
    $args = [
        'post_type'   => 'attachment',
        'post_status' => 'inherit',
        'meta_query'  => [
            [
                'key'     => '_wp_attached_file',
                'value'   => $filename,
                'compare' => 'LIKE'
            ]
        ]
    ];
    $query = new WP_Query($args);
    foreach ($query->posts as $attachment) {
        $file = get_post_meta($attachment->ID, '_wp_attached_file', true);
        if (basename($file) === $filename) {
            return $attachment->ID;
        }
    }
    return false;
}

function espr_sideload_file($url, $post_id) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = download_url($url);
    if (is_wp_error($tmp)) return $tmp;

    $file_array = [
        'name'     => basename($url),
        'tmp_name' => $tmp,
    ];

    add_filter('upload_mimes', 'espr_allow_svg_mime');
    $id = media_handle_sideload($file_array, $post_id);
    remove_filter('upload_mimes', 'espr_allow_svg_mime');

    if (is_wp_error($id)) {
        @unlink($file_array['tmp_name']);
        return false;
    }

    return $id;
}

function espr_allow_svg_mime($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
