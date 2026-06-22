<?php

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:sanctum']], function () {
    Route::post('biometric-attendance', 'BiometricAttendanceController@store')->name('biometric-attendance.store');

    // Epaper
    Route::post('epapers/media', 'EpaperApiController@storeMedia')->name('epapers.storeMedia');
    Route::apiResource('epapers', 'EpaperApiController');
});

Route::group([
    'namespace' => 'Api\V1\Admin'
], function () {

    Route::post('heartbeat', 'DeviceApiController@heartbeat');
    Route::post('attendance', 'DeviceApiController@attendance');
});
