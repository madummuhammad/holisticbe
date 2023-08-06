<?php 
$router->group([
    'prefix' => 'place',
], function ($router) {
    $router->get('province', 'RajaongkirController@province');
    $router->get('city', 'RajaongkirController@city');
});

$router->group([
    'prefix' => 'book',
    'middleware'=>'auth:api'
], function ($router) {
    $router->post('create', 'BookController@create');
    $router->get('check', 'BookController@check');
});

$router->group([
    'prefix' => 'donation',
    'middleware'=>'auth:api'
], function ($router) {
    $router->post('upload', 'DonationController@upload');
    $router->post('pay', 'DonationController@pay');
    $router->get('unpaid', 'DonationController@unpaid');
});

$router->group([
    'prefix' => 'schedule',
    'middleware'=>'auth:api'
], function ($router) {
    $router->get('list', 'ScheduleController@list');
    $router->post('rate', 'ScheduleController@rate');
    $router->get('complete', 'ScheduleController@complete');
    $router->get('upcoming', 'ScheduleController@upcoming');
    $router->post('accept', 'ScheduleController@accept');
    $router->post('reject', 'ScheduleController@reject');
    $router->post('finish', 'ScheduleController@finish');
});

$router->group([
    'prefix' => 'auth',
], function ($router) {
    $router->post('register', 'AuthController@register');
    $router->post('contact_us', 'AuthController@contact_us');
    $router->post('forgot_password', 'AuthController@forgot_password');
    $router->post('login', 'AuthController@login');
    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->get('account', 'AuthController@account');
        $router->post('edit_account', 'AuthController@edit_account');
    });
});

$router->group([
    'prefix' => 'service',
], function ($router) {
    $router->get('detail', 'ServiceController@detail');
    $router->get('list', 'ServiceController@list');
    $router->post('filter', 'ServiceController@filter');
    $router->get('all', 'ServiceController@all');
    $router->get('city', 'ServiceController@city');
    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->post('edit', 'ServiceController@edit');
        $router->delete('delete/{id}', 'ServiceController@delete');
    });
});


$router->group([
    'prefix' => 'category',
], function ($router) {
    $router->get('service', 'ServiceCategoryController@list');
    $router->get('product', 'ProductCategoryController@list');
});

$router->group([
    'prefix' => 'event',
], function ($router) {
    $router->get('/list', 'EventController@list');
    $router->get('/{id}', 'EventController@detail');
});

$router->group([
    'prefix' => 'product',
], function ($router) {
    $router->get('new', 'ProductController@new');
    $router->get('detail', 'ProductController@detail');
    $router->get('list', 'ProductController@list');
    $router->post('filter', 'ProductController@filter');
    $router->get('all', 'ProductController@all');
    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->post('edit', 'ProductController@edit');
        $router->post('create', 'ProductController@create');
        $router->post('upload', 'ProductController@image_product');
        $router->delete('delete/{id}', 'ProductController@delete');
    });
});

$router->group([
    'prefix' => 'account',
], function ($router) {
    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->group([
            'prefix' => 'service',
        ], function ($router) {
            $router->get('', 'AccountController@service');
            $router->post('create', 'AccountController@create_service');
            $router->post('image', 'AccountController@image_service');
        });

        $router->group([
            'prefix' => 'product',
        ], function ($router) {
            $router->get('', 'AccountController@product');
        });
    });
});

$router->group([
    'prefix' => 'slider',
], function ($router) {
    $router->get('homepage', 'SliderController@homepage');
    $router->get('product', 'SliderController@product');
});

$router->group([
    'prefix' => 'practicioner',
], function ($router) {
    $router->get('new', 'UserController@new');
    $router->get('all', 'UserController@all');
    $router->get('favorite', 'UserController@favorite');
    $router->get('detail', 'UserController@detail');
});
?>