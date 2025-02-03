<?php
add_action( 'init', 'register_post_types' );
function register_post_types(){

        $labels = array(
            'name'=> _x('Заявки', 'post type general name'),
            'singular_name'=> _x('Заявки', 'post type singular name'),
            'menu_name'=> _x('Заявки', 'admin menu'),
            'name_admin_bar'=> _x('Кастомный Пост', 'add new on admin bar'),
            'add_new'=> _x('Добавить Новый', ''),
            'add_new_item'=> __('Добавить Новую заявку'),
            'new_item'=> __('Новый Кастомный Пост'),
            'edit_item'=> __('Редактировать Кастомный Пост'),
            'view_item'=> __('Просмотреть Кастомный Пост'),
            'all_items'=> __('Все заявки'), 
        );
    
        $args = array(
            'labels'=>$labels,
            'public'=> true,
            'has_archive'=> true,
            'supports'=> ['title', 'editor', 'thumbnail'],
            'rewrite'=> ['slug' =>'custom-posts'], // Укажите слаг для URL
        );
     register_post_type('custom_post', $args);
    add_action('init', 'create_custom_post_type');
    
}
function create_request_post($name, $nickname, $tovarid, $productDetails) {
    $post_id = wp_insert_post(array(
        'post_title' => $name,      
        'post_content' => '',
        'post_status' => 'draft',
        'post_type' => 'custom_post',
        'post_parent' => $tovarid,
    ));

    if ($post_id) {
        update_field('name', $name,$post_id);
        update_field('nickname',$nickname, $post_id);
        update_field('tovar',$tovarid, $post_id);
        
        $repeater_data = array(
            array(
                'field_6790bb56bb248' => 'Размер',
                'field_6790bb8dbb249' => $productDetails['size']
            ),  
            array(
                'field_6790bb56bb248' => 'Цвет',
                'field_6790bb8dbb249' => $productDetails['color']
            ),
            array(
                'field_6790bb56bb248' => 'Материал',
                'field_6790bb8dbb249' => $productDetails['material']
            ),
            array(
                'field_6790bb56bb248' => 'Категория',
                'field_6790bb8dbb249' => $productDetails['category']
            )
        );
        
        update_field('field_6790ba95bb247', $repeater_data, $post_id);
        update_field('group_6788d4bfc74d8', $tovarid, $post_id);
        
       
        return $post_id;
    } else {
        return false;
    }
}
//товары
add_action( 'init', 'register_post_tovar_types' );
function register_post_tovar_types(){
    
        $labels1 = array(
            'name'=> _x('Товары', 'post type general name'),
            'singular_name'=> _x('товары', 'post type singular name'),
            'menu_name'=> _x('Товары', 'admin menu'),
            'name_admin_bar'=> _x('Кастомный Пост товары', 'add new on admin bar'),
            'add_new'=> _x('Добавить Новый товар', ''),
            'add_new_item'=> __('Добавить товар'),
            'new_item'=> __('Новый Товар'),
            'edit_item'=> __('Редактировать Товары'),
            'view_item'=> __('Просмотреть Товары'),
            'all_items'=> __('Все товары'), 
        );
    
        $args1 = array(
            'labels'=>$labels1,
            'public'=> true,
            'has_archive'=> true,
            'supports'=> ['title', 'editor', 'thumbnail'],
            'rewrite'=> ['slug' =>'custom-posts'], // Укажите слаг для URL
        );
    
     register_post_type('custom_post_tovar', $args1);
    add_action('init', 'create_custom_post_tovar_type');
    
}

function create_request_post_tovar($name,$size, $color, $material) {
    $post_id = wp_insert_post(array(
        'post_title' => $name,      
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'custom_post_tovar',
    ));

    if ($post_id) {
        update_field('size', $size,$post_id);
        update_field('color',$color, $post_id);
        update_field('material',$material, $post_id);

        update_field('group_6788d4bfc74d8', $color, $post_id);
        return $post_id;
    } else {
        return false;
    }
}

