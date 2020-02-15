<?php

Route::post('login', 'Auth\AuthController@login');
Route::post('login/social', 'Auth\SocialLoginController@index');
Route::post('logout', 'Auth\AuthController@logout');

Route::group(['middleware' => 'registration'], function () {
    Route::post('register', 'Auth\RegistrationController@index');
    Route::post('register/verify-email/{token}', 'Auth\RegistrationController@verifyEmail');
});

Route::group(['middleware' => 'password-reset'], function () {
    Route::post('password/remind', 'Auth\Password\RemindController@index');
    Route::post('password/reset', 'Auth\Password\ResetController@index');
});

Route::get('stats', 'StatsController@index');

Route::get('me', 'Profile\DetailsController@index');
Route::patch('me/details', 'Profile\DetailsController@update');
Route::patch('me/details/auth', 'Profile\AuthDetailsController@update');
Route::put('me/avatar', 'Profile\AvatarController@update');
Route::delete('me/avatar', 'Profile\AvatarController@destroy');
Route::put('me/avatar/external', 'Profile\AvatarController@updateExternal');
Route::get('me/sessions', 'Profile\SessionsController@index');

Route::group(['middleware' => 'two-factor'], function () {
    Route::put('me/2fa', 'Profile\TwoFactorController@update');
    Route::post('me/2fa/verify', 'Profile\TwoFactorController@verify');
    Route::delete('me/2fa', 'Profile\TwoFactorController@destroy');
});

Route::resource('users', 'Users\UsersController', [
    'except' => 'create'
]);

Route::put('users/{user}/avatar', 'Users\AvatarController@update');
Route::put('users/{user}/avatar/external', 'Users\AvatarController@updateExternal');
Route::delete('users/{user}/avatar', 'Users\AvatarController@destroy');

Route::group(['middleware' => 'two-factor'], function () {
    Route::put('users/{user}/2fa', 'Users\TwoFactorController@update');
    Route::post('users/{user}/2fa/verify', 'Users\TwoFactorController@verify');
    Route::delete('users/{user}/2fa', 'Users\TwoFactorController@destroy');
});

//Route::get('users/{user}/activity', 'Users\ActivityController@index');
Route::get('users/{user}/sessions', 'Users\SessionsController@index');

Route::get('/sessions/{session}', 'SessionsController@show');
Route::delete('/sessions/{session}', 'SessionsController@destroy');

//Route::get('/activity', 'ActivityController@index');

Route::resource('roles', 'Authorization\RolesController', [
    'except' => 'create'
]);
Route::get("roles/{role}/permissions", 'Authorization\RolePermissionsController@show');
Route::put("roles/{role}/permissions", 'Authorization\RolePermissionsController@update');

Route::resource('permissions', 'Authorization\PermissionsController', [
    'except' => 'create'
]);

Route::get('/settings', 'SettingsController@index');

Route::get('/countries', 'CountriesController@index');


// Services Type
Route::resource('service_type', 'Service\ServiceType', [
    'except' => ['show', 'create', 'edit']
]);

// Services
Route::resource('service', 'Service\Service', [
    'except' => ['show', 'create', 'edit']
]);

// Service User
Route::get('user/{id}/service', 'Service\ServiceUser@getListUserServices');
Route::get('service/{id}/user', 'Service\ServiceUser@getListServicesUser');
Route::get('service/{id}/user/{user_id}', 'Service\ServiceUser@hasAttachServices');
Route::group(['middleware' => 'auth'], function () {
    Route::post('user/service/attach', 'Service\ServiceUser@attach');
    Route::post('user/service/detach', 'Service\ServiceUser@detach');
});

// Service Item
Route::resource('service_item', 'Service\ServiceItem', [
    'except' => ['show', 'create', 'edit']
]);

// Region
Route::resource('region', 'Cemetery\Region', [
    'except' => ['show', 'create', 'edit']
]);
