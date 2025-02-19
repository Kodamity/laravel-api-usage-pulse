<?php

use Illuminate\Support\Facades\Route;

Route::get('/test/informational', static fn () => response('', 100));
Route::get('/test/successful', static fn () => response('', 200));
Route::get('/test/redirection', static fn () => response('', 302));
Route::get('/test/client-error', static fn () => response('', 400));
Route::get('/test/server-error', static fn () => response('', 500));

Route::get('/test/ignored/it-can-not-pulse', static fn () => response('', 200));

Route::get('/test/long-response', static function () {
    usleep(random_int(100, 1000) * 1000);

    return response('', 200);
});
