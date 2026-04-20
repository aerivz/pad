@extends('layouts.panel')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3>{{ $stats['alumnos'] }}</h3><p>Total alumnos</p></div><div class="icon"><i class="fas fa-user-graduate"></i></div></div></div>
    <div class="col-lg-3 col-6"><div class="small-box bg-success"><div class="inner"><h3>{{ $stats['secciones'] }}</h3><p>Secciones activas</p></div><div class="icon"><i class="fas fa-school"></i></div></div></div>
    <div class="col-lg-3 col-6"><div class="small-box bg-warning"><div class="inner"><h3>{{ $stats['profesores'] }}</h3><p>Profesores</p></div><div class="icon"><i class="fas fa-chalkboard-teacher"></i></div></div></div>
    <div class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h3>{{ $stats['notas'] }}</h3><p>Notas cargadas</p></div><div class="icon"><i class="fas fa-pencil-alt"></i></div></div></div>
</div>

<div class="callout callout-info">
    <h5><i class="fas fa-layer-group mr-2"></i>Módulos independientes</h5>
    <p>Ahora cada opción del menú abre su propia URL y su propia vista dentro del panel.</p>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Actividad reciente</h3></div>
    <div class="card-body p-0">
        <ul class="products-list product-list-in-card pl-2 pr-2">
            @forelse ($audit as $item)
                <li class="item p-2">
                    <div class="product-img"><i class="fas fa-history fa-lg mt-1 text-info"></i></div>
                    <div class="product-info">
                        <span class="product-title text-sm">{{ $item->accion }} de nota por {{ $item->nombre_usuario }}</span>
                        <span class="product-description text-muted" style="font-size:.75rem;">Alumno #{{ $item->alumno_id }} | Nuevo valor: {{ $item->valor_nuevo ?? '—' }}</span>
                    </div>
                </li>
            @empty
                <li class="p-3 text-muted small">No hay actividad registrada todavía.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
