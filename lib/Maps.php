<?php

namespace PrmMapChanger;

class Maps
{
    /** @return list<array{id:string,name:string,group:string}> */
    public static function all(): array
    {
        return [
            ['id' => 'de_mirage', 'name' => 'Mirage', 'group' => 'Defuse'],
            ['id' => 'de_dust2', 'name' => 'Dust II', 'group' => 'Defuse'],
            ['id' => 'de_inferno', 'name' => 'Inferno', 'group' => 'Defuse'],
            ['id' => 'de_nuke', 'name' => 'Nuke', 'group' => 'Defuse'],
            ['id' => 'de_overpass', 'name' => 'Overpass', 'group' => 'Defuse'],
            ['id' => 'de_ancient', 'name' => 'Ancient', 'group' => 'Defuse'],
            ['id' => 'de_ancient_night', 'name' => 'Ancient Night', 'group' => 'Defuse'],
            ['id' => 'de_anubis', 'name' => 'Anubis', 'group' => 'Defuse'],
            ['id' => 'de_vertigo', 'name' => 'Vertigo', 'group' => 'Defuse'],
            ['id' => 'de_train', 'name' => 'Train', 'group' => 'Defuse'],
            ['id' => 'de_dust', 'name' => 'Dust', 'group' => 'Defuse'],
            ['id' => 'de_golden', 'name' => 'Golden', 'group' => 'Defuse'],
            ['id' => 'de_palacio', 'name' => 'Palacio', 'group' => 'Defuse'],
            ['id' => 'de_rooftop', 'name' => 'Rooftop', 'group' => 'Defuse'],
            ['id' => 'cs_office', 'name' => 'Office', 'group' => 'Hostage'],
            ['id' => 'cs_italy', 'name' => 'Italy', 'group' => 'Hostage'],
            ['id' => 'cs_agency', 'name' => 'Agency', 'group' => 'Hostage'],
            ['id' => 'ar_baggage', 'name' => 'Baggage', 'group' => 'Arms Race'],
            ['id' => 'ar_shoots', 'name' => 'Shoots', 'group' => 'Arms Race'],
            ['id' => 'ar_shoots_night', 'name' => 'Shoots Night', 'group' => 'Arms Race'],
            ['id' => 'ar_pool_day', 'name' => 'Pool Day', 'group' => 'Arms Race'],
        ];
    }

    public static function find(string $id): ?array
    {
        foreach (self::all() as $map) {
            if ($map['id'] === $id) {
                return $map;
            }
        }

        return null;
    }

    public static function imageUrl(string $id): string
    {
        return '/extensions/prmmapchanger/maps/' . rawurlencode($id) . '.png';
    }

    public static function imagePath(string $id): ?string
    {
        $base = dirname(__DIR__) . '/public/maps/' . $id . '.png';
        if (is_file($base)) {
            return $base;
        }
        $mounted = '/blueprint_extensions/prmmapchanger/public/maps/' . $id . '.png';
        if (is_file($mounted)) {
            return $mounted;
        }

        return null;
    }
}
