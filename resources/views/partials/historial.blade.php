@php
    use App\Models\Historial;
    $iconos = [
        'estado_trabajo' => ['bi-activity',         '#0d6efd'],
        'estado'         => ['bi-activity',         '#0d6efd'],
        'responsable_id' => ['bi-person-gear',      '#6f42c1'],
        'fecha_inicio'   => ['bi-calendar-event',   '#198754'],
        'fecha_estimada' => ['bi-calendar-check',   '#fd7e14'],
        'fecha_fin'      => ['bi-calendar-x',       '#dc3545'],
    ];
@endphp

<div class="bg-white rounded shadow-sm card-section mt-3">
    <h6 class="card-section-title">
        <i class="bi bi-clock-history me-1"></i> Historial de Cambios
    </h6>
    <div class="p-3">
        @forelse($historial as $entrada)
        @php
            [$icono, $color] = $iconos[$entrada->campo] ?? ['bi-pencil', '#6c757d'];
            $label = Historial::labelCampo($entrada->campo);
        @endphp
        <div class="d-flex gap-3 mb-3">
            <div class="flex-shrink-0 d-flex align-items-start pt-1">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:28px;height:28px;background:{{ $color }}1a;">
                    <i class="bi {{ $icono }}" style="font-size:.75rem;color:{{ $color }};"></i>
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-semibold small">{{ $label }}</span>
                    @if($entrada->valor_anterior)
                    <span class="badge" style="background:#f0f0f0;color:#555;font-size:.68rem;">
                        {{ $entrada->valor_anterior }}
                    </span>
                    <i class="bi bi-arrow-right" style="font-size:.65rem;color:#aaa;"></i>
                    @endif
                    <span class="badge" style="background:{{ $color }}20;color:{{ $color }};font-size:.68rem;">
                        {{ $entrada->valor_nuevo ?? '—' }}
                    </span>
                </div>
                <div class="d-flex gap-2 mt-1" style="font-size:.7rem;color:#aaa;">
                    <span><i class="bi bi-person me-1"></i>{{ $entrada->usuario?->nombre_completo ?? 'Sistema' }}</span>
                    <span>·</span>
                    <span><i class="bi bi-clock me-1"></i>{{ $entrada->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>
        @empty
        <p class="text-muted small mb-0 text-center py-3">
            <i class="bi bi-clock-history d-block mb-1" style="font-size:1.4rem;"></i>
            Sin cambios registrados aún.
        </p>
        @endforelse
    </div>
</div>
