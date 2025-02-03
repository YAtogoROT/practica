<?php
add_action( 'rest_api_init', function () {
    register_rest_route( 'methods', '/registration', array(
        'methods' => 'POST',
        'callback' => 'user_registration',
        'permission_callback' => '__return_true'
    ) );
} );

function user_registration(WP_REST_Request $request){

    $username = sanitize_user($request->get_param('username'));
    $email = sanitize_email($request->get_param('email'));
    $password = $request->get_param('password');
    $firstname = sanitize_text_field($request->get_param('firstname'));
    $lastname = sanitize_text_field($request->get_param('lastname'));
    $phone = sanitize_text_field($request->get_param('phone'));

    if (username_exists($username) || email_exists($email)) {
        return new WP_Error('user_exists', 'Пользователь с таким именем или email уже существует.', array('status' => 400));
    }

    // Создаем нового пользователя
    $user_id = wp_create_user($username, $password, $email);
    if (is_wp_error($user_id)) {
        return new WP_Error('registration_failed', 'Ошибка регистрации пользователя.', array('status' => 500));
    }

    // Обновляем метаданные пользователя
    update_user_meta($user_id, 'first_name', $firstname);
    update_user_meta($user_id, 'last_name', $lastname);
    update_user_meta($user_id, 'phone', $phone);

    return array('message' => 'Регистрация прошла успешно!', 'user_id' => $user_id);
}
add_action( 'rest_api_init', function () {
    register_rest_route( 'methods/', 'user/me', array(
        'methods' => 'GET',
        'callback' => 'check_user_authorization',
        'permission_callback' => '__return_true'
    ) );
} );
function check_user_authorization() {
    // Проверяем, авторизован ли пользователь
    if (is_user_logged_in()) {
        // Получаем данные текущего пользователя
        $user = wp_get_current_user();
        // Пример вызова функции
        
        // Возвращаем информацию о пользователе
        return array(
            'status' => 'authorized',
            'user_id' => $user->ID,
            'user_email' => $user->user_email,
            'display_name' => $user->display_name,
        );
    } else {
        // Если пользователь не авторизован, возвращаем статус
        return array(
            'status' => 'unauthorized',
            'message' => 'Пользователь не авторизован',
        );
    }
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'methods', '/addtovar', array(
        'methods' => 'POST',
        'callback' => 'add_tovar_in_korzina',
        'permission_callback' => 'is_user_logged_in'
    ) );
} );

function add_tovar_in_korzina(WP_REST_Request $request){
    $token = $request->get_header('Authorization');
    $product_id = $request->get_param('product_id');
    $count = $request->get_param('count');

    // Проверка авторизации
    if (!is_user_logged_in()) {
        return new WP_Error('unauthorized', 'Пользователь не авторизован', array('status' => 401));
    }

    // Проверка существования товара
    if (!get_post($product_id)) {
        return new WP_Error('not_found', 'Товар не найден', array('status' => 404));
    }

    $user_id = get_current_user_id();

    // Получаем текущее значение повторителя
    $repeater = get_field('korzina', 'user_' . $user_id); // Предполагаем, что поле повторителя привязано к пользователю

    // Если повторитель не существует, создаем его
    if (empty($repeater)) {
        $repeater = array();
    }
    $product_found = true;

    // Проверяем, существует ли товар в корзине
    foreach ($repeater as &$product) {
        if ($product['tovarchiki']->ID == $product_id) {
            $product['count'] += $count;
            $product_found = false;
            break; // Выходим из цикла, так как товар найден
        }
    }

    // Если товар не найден, добавляем его в корзину
    if ($product_found) {
        // Создаем массив для вложенных полей
        $new_product = array(
            'tovarchiki' => $product_id,
            'count' => $count
        );

        // Добавляем новый товар в повторитель
        $repeater[] = $new_product;
    }
    // Сохраняем обновленное значение повторителя
    update_field('korzina', $repeater, 'user_' . $user_id);

    return new WP_REST_Response('Товар добавлен в корзину', 200);
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'methods', '/deletetov', array(
        'methods' => 'POST',
        'callback' => 'delete_tovar',
        'permission_callback' => 'is_user_logged_in'
    ) );
} );

function delete_tovar(WP_REST_Request $request) {
    // Получаем ID товара из запроса
    $product_id = $request->get_param('product_id');

    // Проверяем, авторизован ли пользователь
    if (!is_user_logged_in()) {
        return new WP_Error('unauthorized', 'Пользователь не авторизован.', array('status' => 401));
    }

    // Получаем текущего пользователя
    $user_id = get_current_user_id();

    // Получаем текущее значение повторителя
    $repeater = get_field('korzina', 'user_' . $user_id); // Предполагаем, что поле повторителя привязано к пользователю

    // Проверяем, существует ли повторитель
    if (empty($repeater)) {
        return new WP_Error('not_found', 'Повторитель нету.', array('status' => 404));
    }

    // Ищем товар по ID и удаляем его
    foreach ($repeater as $key => $product) {
        // Проверяем, существует ли product_to_cart и его ID
        if (isset($product['tovarchiki']->ID) && $product['tovarchiki']->ID == $product_id) {
            unset($repeater[$key]); // Удаляем товар из массива
            break; // Выходим из цикла, так как товар найден и удален
        }
    }

    // Сохраняем обновленное значение повторителя
    update_field('korzina', array_values($repeater), 'user_' . $user_id); // Используем array_values для переиндексации массива

    return rest_ensure_response(array('message' => 'Товар удален из корзины.'));
}
//http://ip-api.com/json/

add_action( 'rest_api_init', function () {
    register_rest_route( 'methods', '/AddCity', array(
        'methods' => 'POST',
        'callback' => 'get_user_ip',
        'permission_callback' => '__return_true'
    ) );
} );

function get_user_ip(WP_REST_Request $request){
        // Получаем ID пользователя из параметров запроса
        $user_id = $request['user_id'];
        // Получаем IP-адрес пользователя по его ID
        $user_ip = '77.220.50.9';//get_user_ip_by_id($user_id);
        if ($user_ip) {
            // Получаем город по IP
            $city = get_city_by_ip($user_ip);

            update_field('city', $city, 'user_' . $user_id);

            if ($city) {
                return new WP_REST_Response(array(
                    'status' => 'success',
                    'city' => $city,
                    'res' => 'vso horosho'
                ), 200);
            } else {
                return new WP_REST_Response(array(
                    'status' => 'error',
                    'message' => "Не удалось получить город по IP {$user_ip}."
                ), 500);
            }
        } else {
            return new WP_REST_Response(array(
                'status' => 'error',
                'message' => "IP-адрес для пользователя с ID {$user_id} не найден."
            ), 404);
        }
}
function get_user_ip_by_id()
{
     
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
             //check ip from share internet
             $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
             //to check ip is pass from proxy
             $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
             $ip = $_SERVER['REMOTE_ADDR'];
        }
        return apply_filters( 'edd_get_ip', $ip );
   
}
add_shortcode('show_ip', 'wpb_show_ip');

function get_city_by_ip($ip) {
    // URL API для получения информации о IP
    $url = "http://ip-api.com/json/{$ip}";

    // Инициализируем cURL
    $ch = curl_init();

    // Устанавливаем параметры cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Выполняем запрос
    $response = curl_exec($ch);

    // Закрываем cURL
    curl_close($ch);

    // Декодируем JSON-ответ
    $data = json_decode($response, true);

    // Проверяем, успешно ли выполнен запрос
    if ($data['status'] === 'success') {
        return $data['city'];
    } else {
        return false;
    }
}


