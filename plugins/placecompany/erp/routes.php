<?php
//Route::get('{path}', 'Placecompany\Erp\Controllers\SinglePageController@displaySPA')->where('path', '(.*)');

Route::group(
    [
        'prefix' => 'erp/api/auth',
        'namespace' => 'Placecompany\Erp\Controllers',
        'middleware' => ['api'],
    ],
    function () {
        Route::middleware(['jwt.auth'])->group(
            function () {
                Route::get(
                    'me',
                    'GetUserController@getUserAndGroups'
                )->name('api.auth.me');
            }
        );
    }
);
