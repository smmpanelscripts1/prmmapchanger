@php
  $lib = '/blueprint_extensions/prmmapchanger/lib';
  if (is_file($lib . '/Maps.php')) {
      require_once $lib . '/Maps.php';
  } else {
      require_once __DIR__ . '/lib/Maps.php';
  }
  $maps = class_exists(\PrmMapChanger\Maps::class) ? \PrmMapChanger\Maps::all() : [];
@endphp

<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">PRM Map Changer</h3>
      </div>
      <div class="box-body">
        <p>Kullanıcılar sunucu sayfasındaki <strong>Map Changer</strong> sekmesinden haritayı tek tıkla değiştirir.</p>
        <p class="text-muted">Açık sunucuya <code>changelevel</code> gider. Kapalıysa <code>SRCDS_MAP</code> kaydedilir, sonraki start’ta açılır.</p>
        <p><strong>{{ count($maps) }}</strong> harita yüklü. Görseller eklenti <code>public/maps</code> klasöründe.</p>
        <ul>
          @foreach($maps as $map)
            <li><code>{{ $map['id'] }}</code> — {{ $map['name'] }} <span class="text-muted">({{ $map['group'] }})</span></li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
</div>
