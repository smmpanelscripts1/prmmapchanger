# PRM Map Changer

Pterodactyl Blueprint eklentisi. CS2 sunucusunda haritayı panelden tek tıkla değiştirir.

## Ne yapar?

- Client sunucu menüsü: **Map Changer** (`/server/{id}/map-changer`)
- Satırda 4 harita kartı, radar görseli
- Açık sunucuya `changelevel de_mirage` gider
- Kapalıysa `SRCDS_MAP` (veya `CS2_MAP` / `MAP`) kaydedilir; sonraki start’ta o harita açılır

## Kurulum

Kaynak klasör: `/blueprint_extensions/prmmapchanger`  
Identifier: `prmmapchanger`

```bash
blueprint -install prmmapchanger
```

Docker:

```bash
docker exec -it oooo-panel-1 blueprint -install prmmapchanger
```

## Görseller

`public/maps/{map}.png` — `OOOO/map_image/{map}_png.png` dosyalarından kopyalandı (numaralı overlay’ler yok).

## Not

getyourhost Next paneline bağlı değil. Yalnızca Ptero client/admin.
