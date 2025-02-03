<?php
add_action('rest_api_init', function () {
    register_rest_route('methods', '/getTovar', array(
        'methods' => 'GET',
        'callback' => 'getTovar',
        'permission_callback' => '__return_true'
    ));
});

function getTovar(WP_REST_Request $request) {
    // Получаем параметры из запроса

    $color = $request->get_param('color'); // Получаем цвет(а) из параметров запроса
    $size = $request->get_param('size'); // Получаем размер(ы) из параметров запроса
    $material = $request->get_param('material'); // Получаем материал(ы) из параметров запроса
    $category = $request->get_param('category');

    $meta_query = array('relation' => 'AND'); // Начинаем с отношения AND
    $tax_query = array();

    // Если передан цвет, добавляем его в meta_query

    if (!empty($color)) {
        $meta_query[] = array(
            'key' => 'color',
            'value' => $color,
            'compare' => '='
        );
    }

    if (!empty($size)) {
        $meta_query[] = array(
            'key' => 'size',
            'value' => $size,
            'compare' => '='
        );
    }

    if (!empty($material)) {
        $meta_query[] = array(
            'key' => 'material',
            'value' => $material,
            'compare' => '='
        );
    }

    if (!empty($category)) {
        $tax_query[] = array(
            'taxonomy' => 'Category_tovar', 
            'field' => 'slug', 
            'terms' => sanitize_text_field($category) 
        );
    }
    // Формируем аргументы для WP_Query
    $args = array(
        'post_type' => 'custom_post_tovar',
        'meta_query' => $meta_query,
        'tax_query' => $tax_query,
    );

    $query = new WP_Query($args);
    $results = array(); // Массив для хранения результатов

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_data = array(
                'size' => get_post_meta(get_the_ID(), 'size', true), // Получаем значение поля 'size'
                'color' => get_post_meta(get_the_ID(), 'color', true), // Получаем значение поля 'color'
                'material' => get_post_meta(get_the_ID(), 'material', true), // Получаем значение поля 'material'
                'category' => wp_get_post_terms(get_the_ID(), 'Category_tovar', array('fields' => 'names'))
            );

            $results[] = $post_data; // Добавляем данные поста в массив результатов
        }
        wp_reset_postdata(); // Восстановление оригинальных данных поста
    }

    // Возвращаем результаты в формате JSON
    return rest_ensure_response($results);
}

function create_custom_taxonomy() {
    register_taxonomy(
        'Category_tovar', // Имя таксономии
        'custom_post_tovar', // Тип поста, к которому будет применяться таксономия
        array(
            'labels' => array(
                'name' => __('Категории'),
                'singular_name' => __('Категория'),
                'search_items' => __('Искать категории'),
                'all_items' => __('Все категории'),
                'edit_item' => __('Редактировать категорию'),
                'update_item' => __('Обновить категорию'),
                'add_new_item' => __('Добавить новую категорию'),
                'new_item_name' => __('Новое имя категории'),
                'menu_name' => __('Категории'),
            ),
            'hierarchical' => true, // Устанавливаем true для иерархической структуры (как категории)
            'show_ui' => true, // Показывать в админке
            'show_admin_column' => true, // Показывать в колонках админки
            'query_var' => true, // Использовать в запросах
            'rewrite' => array('slug' => 'custom-category_tovar'), // Слаг для URL
        )
    );
}
add_action('init', 'create_custom_taxonomy');