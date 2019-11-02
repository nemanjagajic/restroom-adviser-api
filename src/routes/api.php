<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group whichh
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([ 'namespace' => 'Api' ], function () {
    Route::group([ 'prefix' => 'auth', 'namespace' => 'Auth' ], function () {
        Route::post('register', 'AuthController@register');
        Route::post('login', 'AuthController@login');
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::get('me', 'AuthController@me');

        Route::group([ 'prefix' => 'social' ], function () {
            Route::post('facebook', 'SocialAuthController@handleFacebookLogin');
            Route::post('google', 'SocialAuthController@handleGoogleLogin');
        });
    });

    Route::group([
        'prefix' => 'user',
        'namespace' => 'User'
    ], function () {
        Route::post('forgot-password', 'ForgotPasswordController@forgotPassword');
        Route::post('reset-password', 'ForgotPasswordController@resetPassword');
    });

    Route::group([ 'middleware' => [ 'auth:api' ] ], function () {
        Route::group([
            'prefix' => 'user',
            'namespace' => 'User'
        ], function () {
            Route::post('change-password', 'UserController@changePassword');
            Route::post('/', 'UserController@updateProfile');
        });


        Route::group([
            'prefix' => 'user/{user}',
            'namespace' => 'User'
        ], function () {
            Route::get('/comments', 'UserController@getComments');
            Route::get('/ratings', 'UserController@getRatings');
            Route::post('/comments/{restroom_comment}/like', 'UserController@likeComment');
            Route::post('/comments/{restroom_comment}/unlike', 'UserController@unlikeComment');
        });

        Route::group([
            'prefix' => 'user/{user}/restroom',
        ], function () {
            Route::get('/', 'RestroomController@index');
            Route::post('/', 'RestroomController@store');
            Route::get('/feedRestrooms', 'RestroomController@getFeedRestrooms');
            Route::post('/{restroom}/comments', 'RestroomController@addComment');
            Route::get('/{restroom}/comments', 'RestroomController@getComments');
            Route::post('/{restroom}/ratings', 'RestroomController@addRating');
            Route::get('/{restroom}/ratings', 'RestroomController@getRatings');
            Route::post('/{restroom}/bookmark', 'RestroomController@bookmarkRestroom');
            Route::post('/{restroom}/unbookmark', 'RestroomController@unbookmarkRestroom');
            Route::get('/{restroom}/bookmarks', 'RestroomController@getBookmarks');
            Route::post('/{restroom}/validate', 'RestroomController@validateRestroom');
            Route::post('/{restroom}/invalidate', 'RestroomController@invalidateRestroom');
        });
    });
});
