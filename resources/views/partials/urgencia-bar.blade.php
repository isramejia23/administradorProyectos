@php
    $niveles = ['bajo' => 1, 'medio' => 2, 'alto' => 3];
    $activo  = $niveles[$nivel ?? 'bajo'] ?? 1;
    $colores = [
        1 => '#c87941',   // bajo  — naranja terroso
        2 => '#b8621e',   // medio — naranja oscuro
        3 => '#9c4a0f',   // alto  — naranja quemado
    ];
    $color  = $colores[$activo];
    $labels = [1 => 'Bajo', 2 => 'Medio', 3 => 'Alto'];
@endphp
<div class="urgencia-bar" title="{{ $labels[$activo] }}">
    @for($i = 1; $i <= 3; $i++)
    <span class="urgencia-seg{{ $i <= $activo ? ' active' : '' }}"
          style="{{ $i <= $activo ? "background:{$color};" : '' }}"></span>
    @endfor
</div>