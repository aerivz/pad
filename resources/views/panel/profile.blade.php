@extends('layouts.panel')

@section('title', 'Mi perfil')

@section('content')
@php
    $fullName = trim($profileUser->nombres.' '.$profileUser->apellidos);
    $roleName = ucfirst($profileUser->role?->nombre ?? 'Usuario');
    $teacher = $profileUser->teacher;
    $initials = strtoupper(substr($profileUser->nombres ?: 'U', 0, 1).substr($profileUser->apellidos ?: 'P', 0, 1));
@endphp
<style>
    .profile-shell { max-width: 1180px; margin: 0 auto; }
    .profile-hero { position: relative; overflow: hidden; min-height: 205px; border: 0; border-radius: 1.25rem; background: linear-gradient(120deg, #520004, #820005 46%, #c93043); box-shadow: 0 15px 40px rgba(88, 4, 16, .19); }
    .profile-hero::before { content: ''; position: absolute; width: 460px; height: 460px; border: 1px solid rgba(255,255,255,.18); border-radius: 50%; right: -110px; top: -305px; box-shadow: 0 0 0 58px rgba(255,255,255,.05), 0 0 0 118px rgba(255,255,255,.035); }
    .profile-hero::after { content: ''; position: absolute; width: 280px; height: 280px; border-radius: 50%; background: rgba(255, 255, 255, .06); left: 34%; bottom: -210px; }
    .profile-hero-content { position: relative; z-index: 1; padding: 1.65rem 1.8rem; color: #fff; }
    .profile-hero-content h2 { margin: 0; font-weight: 700; font-size: 1.55rem; }
    .profile-hero-content p { margin: .35rem 0 0; opacity: .84; }
    .profile-identity { position: relative; z-index: 2; margin: -76px 1.5rem 0; padding: 1.15rem 1.35rem 1.15rem 138px; min-height: 125px; border-radius: 1rem; background: #fff; border: 1px solid #edf0f5; box-shadow: 0 12px 28px rgba(15, 23, 42, .09); }
    .profile-avatar { position: absolute; left: 24px; top: -34px; width: 100px; height: 100px; border-radius: 50%; border: 5px solid #fff; object-fit: cover; background: #f7e8ea; box-shadow: 0 8px 22px rgba(15, 23, 42, .13); }
    .profile-avatar-fallback { display: grid; place-items: center; color: #820005; font-size: 2rem; font-weight: 700; }
    .profile-identity h1 { color: #172033; margin: 0; font-size: 1.35rem; font-weight: 700; }
    .profile-identity .profile-subtitle { color: #64748b; font-size: .9rem; margin-top: .25rem; }
    .profile-role { display: inline-flex; align-items: center; gap: .35rem; margin-top: .55rem; border-radius: 999px; padding: .25rem .65rem; background: #fbeaec; color: #820005; font-size: .76rem; font-weight: 700; }
    .profile-edit { position: absolute; right: 1.2rem; top: 1.2rem; }
    .profile-grid { display: grid; grid-template-columns: minmax(280px, .85fr) minmax(0, 1.5fr); gap: 1.25rem; margin-top: 1.25rem; }
    .profile-card { border: 1px solid #e5eaf1; border-radius: 1rem; box-shadow: 0 8px 26px rgba(15, 23, 42, .05); overflow: hidden; }
    .profile-card .card-header { background: #fff; border-bottom: 1px solid #edf0f4; padding: .95rem 1.15rem; font-weight: 700; color: #253248; }
    .profile-card .card-body { padding: 1.1rem; }
    .profile-info-row { display: flex; gap: .75rem; align-items: flex-start; padding: .65rem 0; border-bottom: 1px solid #f0f2f5; }
    .profile-info-row:last-child { border-bottom: 0; }
    .profile-info-icon { width: 30px; color: #820005; text-align: center; padding-top: .1rem; }
    .profile-info-label { color: #7b8799; text-transform: uppercase; letter-spacing: .05em; font-size: .68rem; font-weight: 700; }
    .profile-info-value { color: #27364b; font-size: .9rem; margin-top: .1rem; overflow-wrap: anywhere; }
    .profile-about { color: #4b5a70; line-height: 1.7; white-space: pre-line; }
    .profile-empty { color: #94a3b8; font-style: italic; }
    .profile-security { display: flex; align-items: center; gap: .9rem; padding: 1rem; background: #fff9f9; border: 1px solid #f5dadd; border-radius: .8rem; }
    .profile-security i { color: #820005; font-size: 1.35rem; }
    .profile-meta-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem; }
    .profile-meta { padding: .8rem; border-radius: .75rem; background: #f8fafc; border: 1px solid #e8edf3; }
    .profile-meta span { display: block; color: #7b8799; text-transform: uppercase; font-size: .66rem; letter-spacing: .05em; font-weight: 700; }
    .profile-meta strong { display: block; margin-top: .28rem; color: #26364c; font-size: .88rem; overflow-wrap: anywhere; }
    body.dark-theme .profile-identity { background: #182338 !important; border-color: #2f405b; box-shadow: 0 12px 28px rgba(0, 0, 0, .23); }
    body.dark-theme .profile-avatar { border-color: #182338; box-shadow: 0 8px 22px rgba(0, 0, 0, .3); }
    body.dark-theme .profile-avatar-fallback { background: #322332; color: #ffc2cb; }
    body.dark-theme .profile-identity .profile-subtitle, body.dark-theme .profile-info-label, body.dark-theme .profile-meta span { color: #9cacbf; }
    body.dark-theme .profile-role { background: #3a222c; color: #ffc4cb; }
    body.dark-theme .profile-card { border-color: #2f405b; box-shadow: 0 8px 26px rgba(0, 0, 0, .18); }
    body.dark-theme .profile-card .card-header { background: #1d2a40 !important; border-color: #2f405b; color: #edf4fc !important; }
    body.dark-theme .profile-info-row { border-color: #2f405b; }
    body.dark-theme .profile-info-value, body.dark-theme .profile-about, body.dark-theme .profile-meta strong { color: #dbe4f0; }
    body.dark-theme .profile-empty { color: #9cacbf; }
    body.dark-theme .profile-security { background: #211f32; border-color: #513343; color: #e8edf5; }
    body.dark-theme .profile-meta { background: #1d2a40; border-color: #2f405b; }
    @media (max-width: 767.98px) {
        .profile-hero { min-height: 165px; }
        .profile-hero-content { padding: 1.25rem; }
        .profile-identity { margin: -55px .75rem 0; padding: 1rem 1rem 1rem 102px; min-height: 112px; }
        .profile-avatar { left: 17px; top: -30px; width: 76px; height: 76px; border-width: 4px; font-size: 1.45rem; }
        .profile-identity h1 { font-size: 1.12rem; padding-right: 4.2rem; }
        .profile-edit { right: .8rem; top: .8rem; }
        .profile-edit .button-label { display: none; }
        .profile-grid { grid-template-columns: 1fr; margin-top: .9rem; }
    }
</style>

<div class="profile-shell">
    <section class="profile-hero">
        <div class="profile-hero-content">
            <h2>Espacio personal</h2>
            <p>Mantén tu información actualizada para una mejor comunicación institucional.</p>
        </div>
    </section>

    <section class="profile-identity">
        @if ($profileUser->avatar)
            <img src="{{ $profileUser->avatar_url }}" alt="Foto de {{ $fullName }}" class="profile-avatar">
        @else
            <div class="profile-avatar profile-avatar-fallback">{{ $initials }}</div>
        @endif
        <h1>{{ $fullName }}</h1>
        <div class="profile-subtitle">{{ $teacher?->especialidad ? $teacher->especialidad.' · ' : '' }}{{ $roleName }}</div>
        <span class="profile-role"><i class="fas fa-shield-alt"></i>{{ $roleName }}</span>
        <button class="btn btn-sm btn-danger profile-edit" type="button" data-toggle="modal" data-target="#profileEditModal"><i class="fas fa-pen mr-1"></i><span class="button-label">Editar perfil</span></button>
    </section>

    <div class="profile-grid">
        <aside>
            <div class="card profile-card mb-3">
                <div class="card-header"><i class="fas fa-address-card mr-2" style="color:#820005;"></i>Contacto</div>
                <div class="card-body">
                    <div class="profile-info-row"><span class="profile-info-icon"><i class="fas fa-at"></i></span><div><div class="profile-info-label">Correo institucional</div><div class="profile-info-value">{{ $profileUser->email }}</div></div></div>
                    <div class="profile-info-row"><span class="profile-info-icon"><i class="fas fa-phone"></i></span><div><div class="profile-info-label">Teléfono</div><div class="profile-info-value">{{ $profileUser->telefono ?: 'No registrado' }}</div></div></div>
                    <div class="profile-info-row"><span class="profile-info-icon"><i class="fas fa-map-marker-alt"></i></span><div><div class="profile-info-label">Ubicación</div><div class="profile-info-value">{{ $profileUser->ubicacion ?: 'No registrada' }}</div></div></div>
                </div>
            </div>
            <div class="card profile-card">
                <div class="card-header"><i class="fas fa-user-cog mr-2" style="color:#820005;"></i>Cuenta</div>
                <div class="card-body">
                    <div class="profile-info-row"><span class="profile-info-icon"><i class="fas fa-user"></i></span><div><div class="profile-info-label">Usuario</div><div class="profile-info-value">{{ $profileUser->nombre_usuario }}</div></div></div>
                    <div class="profile-info-row"><span class="profile-info-icon"><i class="fas fa-user-tag"></i></span><div><div class="profile-info-label">Perfil de acceso</div><div class="profile-info-value">{{ $roleName }}</div></div></div>
                    <div class="profile-info-row"><span class="profile-info-icon"><i class="fas fa-calendar-check"></i></span><div><div class="profile-info-label">Miembro desde</div><div class="profile-info-value">{{ optional($profileUser->created_at)->format('d/m/Y') ?: 'No disponible' }}</div></div></div>
                </div>
            </div>
        </aside>

        <main>
            <div class="card profile-card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center"><span><i class="fas fa-user mr-2" style="color:#820005;"></i>Sobre mí</span><button class="btn btn-link btn-sm text-muted" type="button" data-toggle="modal" data-target="#profileEditModal"><i class="fas fa-pen"></i></button></div>
                <div class="card-body"><div class="profile-about {{ $profileUser->biografia ? '' : 'profile-empty' }}">{{ $profileUser->biografia ?: 'Agrega una breve descripción personal o profesional.' }}</div></div>
            </div>
            <div class="card profile-card mb-3">
                <div class="card-header"><i class="fas fa-briefcase mr-2" style="color:#820005;"></i>Información académica</div>
                <div class="card-body">
                    <div class="profile-meta-grid">
                        <div class="profile-meta"><span>Perfil</span><strong>{{ $roleName }}</strong></div>
                        <div class="profile-meta"><span>Especialidad</span><strong>{{ $teacher?->especialidad ?: 'No aplica' }}</strong></div>
                        <div class="profile-meta"><span>Correo</span><strong>{{ $profileUser->email }}</strong></div>
                        <div class="profile-meta"><span>Estado</span><strong class="text-success">Activo</strong></div>
                    </div>
                </div>
            </div>
            <div class="profile-security"><i class="fas fa-lock"></i><div><strong>Seguridad de cuenta</strong><div class="small text-muted">Puedes actualizar tu contraseña desde Editar perfil. Se solicitará tu contraseña actual.</div></div></div>
        </main>
    </div>
</div>

<div class="modal fade" id="profileEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Editar mi perfil</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <form method="POST" action="{{ \App\Support\AppUrl::route('profile.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 form-group"><label>Nombres</label><input name="nombres" class="form-control" value="{{ old('nombres', $profileUser->nombres) }}" required></div>
                    <div class="col-md-6 form-group"><label>Apellidos</label><input name="apellidos" class="form-control" value="{{ old('apellidos', $profileUser->apellidos) }}" required></div>
                    <div class="col-md-6 form-group"><label>Correo</label><input type="email" name="email" class="form-control" value="{{ old('email', $profileUser->email) }}" required></div>
                    <div class="col-md-6 form-group"><label>Teléfono</label><input name="telefono" class="form-control" value="{{ old('telefono', $profileUser->telefono) }}"></div>
                    <div class="col-md-12 form-group"><label>Ubicación</label><input name="ubicacion" class="form-control" value="{{ old('ubicacion', $profileUser->ubicacion) }}" placeholder="Ejemplo: San Salvador, El Salvador"></div>
                    <div class="col-md-12 form-group"><label>Biografía</label><textarea name="biografia" class="form-control" rows="4" maxlength="1000" placeholder="Una breve descripción personal o profesional">{{ old('biografia', $profileUser->biografia) }}</textarea></div>
                    <div class="col-md-12 form-group"><label>Foto de perfil</label><input type="file" name="avatar" class="form-control-file" accept="image/png,image/jpeg,image/webp"><small class="text-muted">JPG, PNG o WebP. Máximo 2 MB.</small></div>
                </div>
                <hr>
                <h6 class="font-weight-bold">Cambiar contraseña <small class="text-muted font-weight-normal">Opcional</small></h6>
                <div class="row">
                    <div class="col-md-4 form-group"><label>Contraseña actual</label><input type="password" name="password_actual" class="form-control"></div>
                    <div class="col-md-4 form-group"><label>Nueva contraseña</label><input type="password" name="password" class="form-control" minlength="8"></div>
                    <div class="col-md-4 form-group"><label>Confirmar contraseña</label><input type="password" name="password_confirmation" class="form-control" minlength="8"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button><button class="btn btn-danger"><i class="fas fa-save mr-1"></i>Guardar perfil</button></div>
        </form>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if ($errors->any())
    $('#profileEditModal').modal('show');
    @endif
});
</script>
@endpush
