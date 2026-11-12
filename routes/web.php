<?php

use Illuminate\Support\Facades\Route;
use PrmMapChanger\Maps;
use Pterodactyl\Http\Middleware\AdminAuthenticate;

$lib = '/blueprint_extensions/prmmapchanger/lib';
if (!is_dir($lib)) {
    $lib = dirname(__DIR__) . '/lib';
}
require_once $lib . '/Maps.php';

Route::get('/maps/{map}.png', function (string $map) {
    if (!preg_match('/^[a-z0-9_]+$/i', $map)) {
        abort(404);
    }
    $path = Maps::imagePath(strtolower($map));
    if (!$path) {
        abort(404);
    }

    return response()->file($path, ['Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=86400']);
})->where('map', '[A-Za-z0-9_]+');

Route::middleware(['web', 'auth', AdminAuthenticate::class])->group(function () {
    Route::get('/admin-ping', function () {
        return response()->json(['ok' => true, 'maps' => count(Maps::all())]);
    });
});
