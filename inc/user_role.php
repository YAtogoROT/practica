<?php
add_action( 'rest_api_init', function () {
    register_rest_route( 'methods', '/add_role', array(
        'methods' => 'POST',
        'callback' => 'add_custom_user_roles',
        'permission_callback' => '__return_true'
    ) );
} );
add_action('init', 'add_custom_user_roles');
function add_custom_user_roles() {
    add_role(
        'Noob', // Системное имя роли
        __('Noob'), // Отображаемое имя роли
        array(
            'read' => true, // Возможность читать
            'edit_posts' => false, // Возможность редактировать посты
            'delete_posts' => false, // Возможность удалять посты
            'publish_posts' => false, // Возможность публиковать посты
        )
    );
    
}
function remove_custom_role() {
    remove_role('Adventure'); // Замените 'your_custom_role' на имя вашей роли
    remove_role('Pro');
    remove_role('Cheater');
}
add_action('init', 'remove_custom_role');