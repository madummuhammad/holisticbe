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
    'prefix' => 'auth',
], function ($router) {
    $router->post('register', 'AuthController@register');
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
    'prefix' => 'product',
], function ($router) {
    $router->get('new', 'ProductController@new');
    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->post('edit', 'ProductController@edit');
        $router->post('create', 'ProductController@create');
        $router->get('detail', 'ProductController@detail');
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
    $router->get('homepage', 'SLiderController@homepage');
    $router->get('product', 'SLiderController@product');
});

$router->group([
    'prefix' => 'practicioner',
], function ($router) {
    $router->get('new', 'UserController@new');
});
?>