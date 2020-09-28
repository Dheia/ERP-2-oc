<?php
Route::get('{path}', 'Placecompany\Erd\Controllers\SinglePageController@displaySPA')->where('path', '(.*)');
