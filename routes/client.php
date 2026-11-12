<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PrmMapChanger\Changer;
use PrmMapChanger\Maps;
use Pterodactyl\Http\Middleware\Api\Client\Server\AuthenticateServerAccess;
use Pterodactyl\Models\Server;

$lib = '/blueprint_extensions/prmmapchanger/lib';
if (!is_dir($lib)) {
    $lib = dirname(__DIR__) . '/lib';
}
require_once $lib . '/Maps.php';
require_once $lib . '/Changer.php';

Route::middleware([AuthenticateServerAccess::class])->group(function () {
    Route::get('/servers/{server}/maps', function (Request $request, Server $server) {
        $current = Changer::currentMap($server);
        $items = array_map(static function (array $map) use ($current) {
            return [
                'id' => $map['id'],
                'name' => $map['name'],
                'group' => $map['group'],
                'image' => Maps::imageUrl($map['id']),
                'current' => $current !== null && strcasecmp($current, $map['id']) === 0,
            ];
        }, Maps::all());

        return response()->json([
            'current' => $current,
            'count' => count($items),
            'items' => $items,
        ]);
    });

    Route::get('/servers/{server}/maps/{map}/image', function (Server $server, string $map) {
        if (!preg_match('/^[a-z0-9_]+$/i', $map)) {
            abort(404);
        }
        $path = Maps::imagePath(strtolower($map));
        if (!$path) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    });

    Route::post('/servers/{server}/change', function (Request $request, Server $server) {
        $map = trim((string) $request->input('map', ''));
        if ($map === '' || !preg_match('/^[a-z0-9_]+$/i', $map)) {
            return response()->json(['error' => 'map gerekli'], 422);
        }

        try {
            $result = Changer::change($server, strtolower($map));

            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });
});
