<?php
/*
Plugin Name: Custom ad posts plugin
Plugin URI:
Description: Post ad posts on wordpress
Version: 1.0.0
Author: Next-Generation
Author URI:
*/

add_action('init','ad_posts_create_post_type');
add_shortcode('ad_submit', 'ad_posts_submit');
add_shortcode('ad_list', 'ad_posts_list');

function ad_posts_tmpl($file, $data = null) {
    if ($data != null)
        extract($data);

    include($file.".tmpl.php");
}

function ad_posts_submit() {
    ad_posts_tmpl('form-template');
}

function ad_posts_list() {
    $posts = get_ad_posts();

    $data = [];

    foreach ($posts as $post) {
        $meta   = get_post_meta($post->ID, 'ad_posts_info');
        $expire = get_post_meta($post->ID, 'ad_posts_expire');

        if (!isset($meta[0]) || !isset($expire[0])) {
            return;
        }

        $info = json_decode($meta[0], true, 512, JSON_UNESCAPED_UNICODE);
        $info['expire'] = $expire[0];
        $info['title'] = $post->post_title;
        $info['text'] = $post->post_content;
        $info['date'] = $post->post_date;
        $data[] = $info;
    }

    ad_posts_tmpl('ad-posts-list', array('data' => $data));
}

function ad_posts_create_post_type() {
    register_post_type('ad_posts',
        array(
            'labels' => array(
                'name' => 'Annonser',
                'singular_name' => 'Annonse',
            ),
            'public' => true,
            'has_archive' => true,
        )
    );

    add_meta_box('ad_posts_info', 'Annonse info', function($args) { 
        $meta   = get_post_meta($args->ID, 'ad_posts_info');
        $expire = get_post_meta($args->ID, 'ad_posts_expire');

        if (!isset($meta[0]) || !isset($expire[0])) {
            return;
        }

        $data = json_decode($meta[0], true);
        $data['expire'] = $expire[0];
        ad_posts_tmpl('show-info', $data);
    }, 'ad_posts');
}

function get_ad_posts($args = null) {
    $defaults = array(
        'numberposts' => 10000,
        'category' => 0, 'orderby' => 'date',
        'order' => 'DESC', 'include' => array(),
        'exclude' => array(), 'meta_key' => '',
        'meta_value' =>'', 'post_type' => 'ad_posts',
        'suppress_filters' => true
    );

    $r = wp_parse_args($args, $defaults);
    if ( empty( $r['post_status'] ) )
        $r['post_status'] = ( 'attachment' == $r['post_type'] ) ? 'inherit' : 'publish';
    if ( ! empty($r['numberposts']) && empty($r['posts_per_page']) )
        $r['posts_per_page'] = $r['numberposts'];
    if ( ! empty($r['category']) )
        $r['cat'] = $r['category'];
    if ( ! empty($r['include']) ) {
        $incposts = wp_parse_id_list( $r['include'] );
        $r['posts_per_page'] = count($incposts);
        $r['post__in'] = $incposts;
    } elseif ( ! empty($r['exclude']) )
        $r['post__not_in'] = wp_parse_id_list( $r['exclude'] );

    $r['ignore_sticky_posts'] = true;
    $r['no_found_rows'] = true;

    $get_posts = new WP_Query;

    $today = time();
    $r['meta_query'] = array(
        array(
            'key' => 'ad_posts_expire',
            'value' => $today,
            'compare' => '>',
            'type' => 'NUMERIC',
        )
    );

    return $get_posts->query($r);
}

