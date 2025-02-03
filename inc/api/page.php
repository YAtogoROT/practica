<?php
add_action( 'rest_api_init', function () {
    register_rest_route( 'methods', '/page', array(
        'methods' => 'GET',
        'callback' => 'getPage',
        'permission_callback' => '__return_true',
    ) );
} );
function getPage(WP_REST_Request $request) {
    $link = $request->get_param('path'); // Получаем параметр "Ссылка"

    // Проверяем наличие ссылки и получаем ID страницы
    $page_id = url_to_postid($link);

    if (!$page_id) {
        return new WP_REST_Response(array('error' => 'Page not found'), 404);
    }

    // Получаем объект поста по ID
    $page = get_post($page_id);

    if (!$page) {
        return new WP_REST_Response(array('error' => 'Page not found'), 404);
    }
    //Подготавливаем данные страницы
    $data = array(
        'id' => $page_id,
        'title' => get_the_title($page_id),
        'content' => apply_filters('the_content', get_post_field('post_content', $page_id)),
        'date' => get_the_date('', $page_id),
        'fields' =>[
        'status' => get_post_status($page_id),
        'author' => get_the_author_meta('display_name', $page->post_author),
        'link' => get_permalink($page_id),
        'parents'=> get_post_field('post_parent',$page_id),
        'discussion_status' => get_post_meta($page_id, 'comment_status', true),
        'acf_fields' => $acf_fields = get_fields($page_id) ?: []]
        
    );
    return new WP_REST_Response($data, 0);
}

