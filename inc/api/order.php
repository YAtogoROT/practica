<?php
add_action( 'rest_api_init', function () {
    register_rest_route( 'methods', '/createRequest', array(
        'methods' => 'POST',
        'callback' => 'createRequest',
        'permission_callback' => '__return_true'
    ) );
} );

function createRequest( WP_REST_Request $request ) {
    $params = $request->get_params();
    // Извлекаем параметры name и nickname из запроса
    $name = isset($params['name']) ? $params['name'] : '';
    $nickname = isset($params['nickname']) ? $params['nickname'] : '';
    $tovarid  = intval($request->get_param('tovar'));
    $productDetails = get_product_details($tovarid);
    if (!$productDetails) {
        return new WP_REST_Response(array('error' => 'Товар не найден'), 404);
    }
    $post_id = create_request_post($name, $nickname, $tovarid, $productDetails);
    
    
    if (!$productDetails) {
        return new WP_REST_Response(array('error' => 'Товар не найден'), 404);
    }
    
    // Логика отправки данных в бо                                                  та
    if ($post_id) {
        try {
            // Отправляем информацию о товаре в Telegram
            sendToTelegram($name, $nickname, $productDetails);
            return new WP_REST_Response(array('OK' => $post_id), 200);
        } catch (\Throwable $th) {
            error_log('Ошибка при отправке в Telegram: ' . $th->getMessage());
        }
    } else {
        return new WP_REST_Response(array('error' => 'Не удалось создать пост'), 400);
    }
    return new WP_REST_Response(array('success' => true), 200);
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'methods', '/createRequestTovar', array(
        'methods' => 'POST',
        'callback' => 'createRequestTovar',
        'permission_callback' => '__return_true'
    ) );
} );

function createRequestTovar( WP_REST_Request $request ) {
    $params = $request->get_params();
    // Извлекаем параметры name и nickname из запроса
    $name = isset($params['name']) ? $params['name'] : '';
    $size = isset($params['size']) ? $params['size'] : '';
    $color = isset($params['color']) ? $params['color'] : '';
    $material = isset($params['material']) ? $params['material'] : '';

    $post_id = create_request_post_tovar($name,$size, $color, $material);
    
    // Логика отправки данных в бо                                                  та
    if ($post_id) {
} else {
    return new WP_REST_Response(array('error' => 'Не удалось создать пост'), 400);
    }

    return new WP_REST_Response(array('success' => true), 200);
}


function sendToTelegram($name, $nickname, $productDetails)
{
    $token = '7768914167:AAGTtYxEHqtjyt7eGxFvZa8npHJ0fYSf8_M';
    $chatId = '1065002972';
    $url = "https://api.telegram.org/bot$token/sendMessage";
    $text = "Имя: $name\nНик: $nickname\n";
    if ($productDetails) {
        $text .= "Товар:\n";
        $text .= "Название: " . $productDetails['title'] . "\n";
        $text .= "Размер: " . $productDetails['size'] . "\n";
        $text .= "Цвет: " . $productDetails['color'] . "\n";
        $text .= "Материал: " . $productDetails['material'] . "\n";
        $text .= "Категория: " . $productDetails['category'] . "\n";
    }
    $data = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'Markdown'
    ];

    // Инициализация cURL
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true,
    ]);

    // Выполнение запроса
    $response = curl_exec($ch);

    // Проверка на ошибки cURL
    if ($response === false) {
        error_log('Curl error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }

    // Закрытие cURL
    curl_close($ch);

    // Декодирование ответа
    $result = json_decode($response, true);

    // Проверка результата
    if (isset($result['ok']) && $result['ok']) {
        return true;
    } else {
        error_log('Ошибка: ' . print_r($result, true));
        return false;
    }
}

function get_product_details($tovarid) {
    $product = get_post($tovarid);
    if ($product) {
        // Получаем термины для таксономии 'product_category'
        $terms = get_the_terms($tovarid, 'Category_tovar');
        
        // Проверяем, есть ли термины и не произошла ли ошибка
        if (!empty($terms) && !is_wp_error($terms)) {
            // Получаем название первой категории
            $category = $terms[0]->name;
        } else {
            $category = 'Не указана'; // Если нет категорий
        }

        return array(
            'title' => $product->post_title,
            'size' => get_post_meta($tovarid, 'size', true) ?: 'Не указано',
            'color' => get_post_meta($tovarid, 'color', true) ?: 'Не указано',
            'material' => get_post_meta($tovarid, 'material', true) ?: 'Не указано',
            'category' => $category
        );
    }
    return null;
}

//тут смотри всю инфу
//http://localhost/wordpress/?rest_route=/

//eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0L3dvcmRwcmVzcyIsImlhdCI6MTczNzYzMDI2MywibmJmIjoxNzM3NjMwMjYzLCJleHAiOjE3MzgyMzUwNjMsImRhdGEiOnsidXNlciI6eyJpZCI6IjExIn19fQ.fwCR9-Xl6lqd2mUuzOmrQa2125cr8v3IR6lkn2tVGRs

add_action( 'rest_api_init', function () {
    register_rest_route( 'methods', '/update_tovar', array(
        'methods' => 'POST',
        'callback' => 'update_tovar',
        'permission_callback' => '__return_true'
    ) );
} );

function update_tovar($data) {
    $params = $data -> get_params();

    $size = isset($params['size']) ? $params['size'] : '';
    $color = isset($params['color']) ? $params['color'] : '';
    $material = isset($params['material']) ? $params['material'] : '';
    $category = isset($params['category']) ? $params['category'] : '';
    $tovarid  = intval($data->get_param('tovar'));  

    $user = wp_get_current_user();
    $user_role = get_user_meta($user->ID);
    if ($user_role['role'][0] !== 'Admin' && $user_role['role'][0] !== 'Main admin') {
        return new WP_Error('permission_denied', 'У вас нет прав на обновление)))', array('status' => 403));
    }
    else{
        update_field('size', $size,$tovarid);
        update_field('color', $color,$tovarid);
        update_field('material', $material,$tovarid);

        if (!empty($category)) {
            $term = term_exists($category, 'Category_tovar'); // Замените 'Category_tovar' на вашу таксономию
            if ($term) {
                wp_set_object_terms($tovarid, $category, 'Category_tovar', false);
            } else {
                return new WP_Error('term_not_found', 'Категория не найдена', array('status' => 404));
            }
        }
        
    }
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'methods', '/update_page', array(
        'methods' => 'POST',
        'callback' => 'update_page',
        'permission_callback' => '__return_true'
    ) );
} );

function update_page(WP_REST_Request $request ){
    $params = $request -> get_params();

    $page_id = isset($params['id']) ? $params['id'] : '';
    $title = isset($params['title']) ? $params['title'] : '';
    $content = isset($params['content']) ? $params['content'] : '';
    $name = isset($params['name']) ? $params['name'] : '';
    $nickname = isset($params['nickname']) ? $params['nickname'] : '';
    
    $page = get_post($page_id);
    
    $user = wp_get_current_user();
    $user_role = get_user_meta($user->ID);
    if ( !$page || $page->post_type !== 'page' ) {
        return new WP_Error('no_page', 'Страница не страница', array('status' => 404));
    }
    if ($user_role['role'][0] !== 'Main admin') {
        return new WP_Error('permission_denied', 'У вас нет прав на обновление)))', array('status' => 403));
    }
    else{
    $updated_post = array(
        'ID'           => $page_id,
        'post_title'   => $title,
        'post_content' => $content,
    );  
    $result = wp_update_post($updated_post);

    if (is_wp_error($result)) {
        return new WP_Error('update_failed', 'Ошибка обновления: ' . $result->get_error_message(), array('status' => 500));
    }

    update_field('field_678a197837e0b', $name, $page_id);
    update_field('field_678a19b937e0c', $nickname, $page_id);

    return new WP_REST_Response('Страница успешно обновлена!', 200);
    }

}

