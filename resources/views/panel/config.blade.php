@extends('layouts.panel')

@section('title', 'Configuración')

@section('content')
@if (! $canManageSystemSettings)
    <div class="alert alert-warning">Solo el administrador puede modificar las credenciales de integracion.</div>
@else
    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ \App\Support\AppUrl::route('config.update') }}" class="card maint-card">
                @csrf
                @method('PUT')
                <div class="card-header"><h3 class="card-title"><i class="fas fa-envelope mr-2"></i>Correo saliente SMTP</h3></div>
                <div class="card-body">
                    <div class="alert alert-info py-2">Los datos reemplazan las variables de correo y no requieren editar <code>.env</code>. Contraseñas y tokens se guardan cifrados.</div>
                    <div class="row">
                        <div class="col-md-3 form-group"><label>Metodo</label><select name="mail_mailer" class="form-control"><option value="smtp" @selected(old('mail_mailer', $systemSettings['mail_mailer']) === 'smtp')>SMTP</option><option value="log" @selected(old('mail_mailer', $systemSettings['mail_mailer']) === 'log')>Solo registro</option></select></div>
                        <div class="col-md-5 form-group"><label>Servidor SMTP</label><input name="mail_host" class="form-control" value="{{ old('mail_host', $systemSettings['mail_host']) }}" placeholder="smtp.ejemplo.com"></div>
                        <div class="col-md-2 form-group"><label>Puerto</label><input type="number" name="mail_port" class="form-control" value="{{ old('mail_port', $systemSettings['mail_port']) }}" placeholder="587"></div>
                        <div class="col-md-2 form-group"><label>Seguridad</label><select name="mail_scheme" class="form-control"><option value="">Ninguna</option><option value="tls" @selected(old('mail_scheme', $systemSettings['mail_scheme']) === 'tls')>TLS</option><option value="ssl" @selected(old('mail_scheme', $systemSettings['mail_scheme']) === 'ssl')>SSL</option></select></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group"><label>Usuario SMTP</label><input name="mail_username" class="form-control" value="{{ old('mail_username', $systemSettings['mail_username']) }}" autocomplete="off"></div>
                        <div class="col-md-6 form-group"><label>Contrasena SMTP @if($systemSettings['mail_password_configured'])<small class="text-success">Configurada</small>@endif</label><input type="password" name="mail_password" class="form-control" placeholder="Dejar vacio para conservar la actual" autocomplete="new-password"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group"><label>Correo remitente</label><input type="email" name="mail_from_address" class="form-control" value="{{ old('mail_from_address', $systemSettings['mail_from_address']) }}"></div>
                        <div class="col-md-6 form-group"><label>Nombre remitente</label><input name="mail_from_name" class="form-control" value="{{ old('mail_from_name', $systemSettings['mail_from_name']) }}"></div>
                    </div>
                    <div class="row"><div class="col-md-4 form-group mb-0"><label>Maximo de correos por minuto</label><input type="number" name="mail_rate_limit_per_minute" min="1" max="300" required class="form-control" value="{{ old('mail_rate_limit_per_minute', $systemSettings['mail_rate_limit_per_minute']) }}"><small class="text-muted">Reduce el valor si tu proveedor SMTP limita envios.</small></div></div>
                </div>
                <div class="card-header border-top"><h3 class="card-title"><i class="fab fa-telegram-plane mr-2"></i>Alertas de errores por Telegram</h3></div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-2 form-group"><div class="custom-control custom-switch mt-2"><input type="checkbox" name="telegram_enabled" value="1" class="custom-control-input" id="telegramEnabled" @checked(old('telegram_enabled', $systemSettings['telegram_enabled']))><label class="custom-control-label" for="telegramEnabled">Activa</label></div></div>
                        <div class="col-md-4 form-group"><label>Chat ID</label><input name="telegram_chat_id" class="form-control" value="{{ old('telegram_chat_id', $systemSettings['telegram_chat_id']) }}"></div>
                        <div class="col-md-4 form-group"><label>Token del bot @if($systemSettings['telegram_token_configured'])<small class="text-success">Configurado</small>@endif</label><input type="password" name="telegram_bot_token" class="form-control" placeholder="Dejar vacio para conservar" autocomplete="new-password"></div>
                        <div class="col-md-2 form-group"><label>Timeout</label><input type="number" name="telegram_timeout" min="1" max="30" class="form-control" value="{{ old('telegram_timeout', $systemSettings['telegram_timeout']) }}"></div>
                    </div>
                </div>
                <div class="card-footer text-right"><button class="btn btn-primary"><i class="fas fa-save mr-1"></i>Guardar configuracion</button></div>
            </form>
        </div>
        <div class="col-lg-4">
            <div class="card maint-card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-vial mr-2"></i>Probar correo</h3></div>
                <form method="POST" action="{{ \App\Support\AppUrl::route('config.email-test') }}" class="card-body">
                    @csrf
                    <p class="text-muted small">Guarda primero la configuracion. El mensaje se enviara con el servidor SMTP activo.</p>
                    <div class="form-group"><label>Destinatario</label><input type="email" name="destinatario" required class="form-control" placeholder="correo@ejemplo.com"></div>
                    <button class="btn btn-outline-primary btn-block"><i class="fas fa-paper-plane mr-1"></i>Enviar prueba</button>
                </form>
            </div>
            <div class="card">
                <div class="card-header"><h3 class="card-title">Variables que siguen en .env</h3></div>
                <div class="card-body small text-muted">Base de datos, clave de aplicacion y configuracion del servidor permanecen en <code>.env</code>, porque se necesitan antes de poder acceder a la base. Esta pantalla reemplaza las integraciones modificables desde el sistema.</div>
            </div>
        </div>
    </div>
@endif
@endsection
