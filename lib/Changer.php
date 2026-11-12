<?php

namespace PrmMapChanger;

use Illuminate\Support\Facades\DB;
use Pterodactyl\Models\Server;
use Pterodactyl\Repositories\Wings\DaemonCommandRepository;
use Pterodactyl\Repositories\Wings\DaemonServerRepository;
use Pterodactyl\Exceptions\Http\Connection\DaemonConnectionException;

class Changer
{
    /** @var list<string> */
    private const MAP_KEYS = ['SRCDS_MAP', 'CS2_MAP', 'MAP'];

    public static function currentMap(Server $server): ?string
    {
        $row = self::mapRow($server);
        if (!$row) {
            return null;
        }
        $value = trim((string) ($row->variable_value ?: $row->default_value ?: ''));

        return $value !== '' ? $value : null;
    }

    public static function persistMap(Server $server, string $map): void
    {
        $row = self::mapRow($server);
        if (!$row) {
            throw new \RuntimeException('Bu sunucuda harita değişkeni (SRCDS_MAP) yok.');
        }

        DB::table('server_variables')->updateOrInsert(
            [
                'server_id' => $server->id,
                'variable_id' => $row->id,
            ],
            [
                'variable_value' => $map,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        try {
            app(DaemonServerRepository::class)->setServer($server->fresh())->sync();
        } catch (\Throwable $e) {
            // Wings sync opsiyonel; DB kaydı asıl kaynak.
        }
    }

    /**
     * @return array{ok:bool,command?:string,offline?:bool,saved:string,message:string}
     */
    public static function change(Server $server, string $map): array
    {
        if (!Maps::find($map)) {
            throw new \InvalidArgumentException('Bilinmeyen harita.');
        }

        self::persistMap($server, $map);

        $commands = [
            'changelevel ' . $map,
            'map ' . $map,
        ];
        try {
            app(DaemonCommandRepository::class)->setServer($server)->send($commands);

            return [
                'ok' => true,
                'saved' => $map,
                'command' => $commands[0],
                'message' => $map . ' kaydedildi ve sunucuya changelevel gönderildi. Konsolda harita yüklenmesini izle.',
            ];
        } catch (DaemonConnectionException $e) {
            return [
                'ok' => true,
                'offline' => true,
                'saved' => $map,
                'command' => $commands[0],
                'message' => 'Sunucu kapalı veya komut gidemedi. ' . $map . ' kaydedildi; sunucuyu başlatınca bu harita açılır.',
            ];
        }
    }

    private static function mapRow(Server $server): ?object
    {
        $rows = DB::table('egg_variables as ev')
            ->leftJoin('server_variables as sv', function ($join) use ($server) {
                $join->on('sv.variable_id', '=', 'ev.id')
                    ->where('sv.server_id', '=', $server->id);
            })
            ->where('ev.egg_id', $server->egg_id)
            ->whereIn('ev.env_variable', self::MAP_KEYS)
            ->select([
                'ev.id',
                'ev.env_variable',
                'ev.default_value',
                'sv.variable_value',
            ])
            ->get();

        foreach (self::MAP_KEYS as $key) {
            $hit = $rows->firstWhere('env_variable', $key);
            if ($hit) {
                return $hit;
            }
        }

        return null;
    }
}
