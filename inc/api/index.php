<?php
add_filter( 'rest_url_prefix', function() { 
    return 'v1'; //Добавляем префикс к апи запросам 
} );

//Телеграм
require_once get_template_directory() . '/inc/api/order.php'; 
//Подключаем файл в котором будет логика передачи данных из формы в телеграм
require_once get_template_directory() . '/inc/api/page.php'; 

require_once get_template_directory() . '/inc/api/user.php';

require_once get_template_directory() . '/inc/api/getTovar.php';

require_once get_template_directory() . '/inc/api/translater_function.php';