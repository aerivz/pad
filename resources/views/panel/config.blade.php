@extends('layouts.panel')

@section('title', 'Configuración')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Resumen técnico</h3></div>
            <div class="card-body">
                <div class="config-note mb-3">El panel quedó separado por módulos con vistas y rutas independientes en `/pad/...`.</div>
                <ul class="mb-0 pl-3">
                    <li>Base objetivo: <strong>pad</strong>.</li>
                    <li>Los CRUD principales ya usan desactivación lógica con el campo <strong>activo</strong>.</li>
                    <li>La navegación del menú ya no depende de tabs ni de anchors con `#`.</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Credenciales demo</h3></div>
            <div class="card-body">
                <p class="mb-2"><strong>Usuario:</strong> admin</p>
                <p class="mb-2"><strong>Correo:</strong> admin@colegio.sv</p>
                <p class="mb-0"><strong>Clave semilla:</strong> 123456</p>
            </div>
        </div>
    </div>
</div>
@endsection
