<?php
App::before(function($request)
{
    // Backend URL
    Route::group(['prefix' => 'board'], function()
    {
        Route::get('media', [
            'as' => 'placecompany.board::media',
            'uses' => 'Placecompany\Board\Controllers\BoardTemplateController@media'
        ]);

        Route::post('mediaUpload', [
            'as' => 'placecompany.board::mediaUpload',
            'uses' => 'Placecompany\Board\Controllers\BoardController@mediaUpload'
        ]);

        Route::post('mediaDelete', [
            'as' => 'placecompany.board::mediaDelete',
            'uses' => 'Placecompany\Board\Controllers\BoardController@mediaDelete'
        ]);

        Route::get('documentPrint', [
            'as' => 'placecompany.board::documentPrint',
            'uses' => 'Placecompany\Board\Controllers\BoardTemplateController@documentPrint'
        ]);


    });
});

