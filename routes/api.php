<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InitiativeController;
use App\Http\Controllers\SubadminController;

Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);
Route::post('subadmins_login', [SubadminController::class, 'loginadmin']);

Route::group([
    'middleware' => 'auth:users'
],function ()
{
    Route::get('me', [UserController::class, 'getAuthenticatedUser']);
    Route::get('get_user/{id}', [UserController::class, 'getUser']);

    Route::post('user_changepass/{id}', [UserController::class, 'changePwd']);
    Route::get('user_delete/{id}', [UserController::class, 'destroy']);
    Route::get('user_logout', [UserController::class, 'logout']);

    Route::get('show_initiative/{id}', [InitiativeController::class, 'show']);
    Route::get('initiatives', [InitiativeController::class, 'index']);
    Route::post('initiative', [InitiativeController::class, 'store']);
    Route::put('update/{id}', [InitiativeController::class, 'update']);
    Route::delete('delete/{id}', [InitiativeController::class, 'destroy']);
});


Route::group([
    'middleware' => 'auth:subadmins'
],function ()
{
    Route::get('subadmin_me', [SubadminController::class, 'getAuthenticatedUser']);
    Route::post('subadmin_register', [SubadminController::class, 'register']);

    /*
    Route::put('subadmin_update/{id}', 'SubadminController@update');
    Route::post('subadmin_changepass/{id}', 'SubadminController@changePwd');
    Route::get('subadmin_me', 'SubadminController@getAuthenticatedUser');
    Route::get('subadmin_logout', 'SubadminController@logout');
    Route::get('subadmin_users', 'SubadminController@allUsers');
    Route::get('subadmin_admins', 'SubadminController@allAdmins');
    Route::delete('subadmin_delete/{id}', 'SubadminController@destroy');
    Route::post('subadmin_register', 'SubadminController@register');

    Route::post('add_blog', 'BlogController@createBlog');
    */

});

   /*
Route::group([
    'middleware' => 'auth:admins'
],function ()
{
    Route::get('admin_me', 'AdminController@getAuthenticatedUser');
    Route::post('admin_register', 'AdminController@register');
    Route::post('subadmin_reg', 'SubadminController@register');

});
 */



Route::get('/person', function() {
    $person = [
        "First_Name" => "Khalil",
        "Last_Name" => "Hisseine"
    ];
    return $person;
});
