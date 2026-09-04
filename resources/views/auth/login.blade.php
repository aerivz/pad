<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso | ACI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    @include('components.pwa-meta')
    <style>
        body {
            min-height: 100vh;
            font-family: 'Source Sans Pro', sans-serif;
            background: linear-gradient(135deg, #230204 0%, #520206 45%, #f3d7dc 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: 0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(130, 0, 5, .22);
        }
        .login-header {
            background: #820005;
            color: #fff;
            padding: 1.5rem;
        }
        .login-header h1 {
            font-size: 1.8rem;
            margin: 0;
            font-weight: 700;
        }
        .login-header p {
            margin: .35rem 0 0;
            opacity: .85;
        }
        .login-logo {
            width: 62px;
            height: 62px;
            object-fit: cover;
            border-radius: 16px;
            margin-bottom: 1rem;
            background: rgba(255,255,255,.1);
            padding: .35rem;
        }
        .pwa-install-button { position: fixed; right: 1rem; bottom: 1rem; z-index: 10; background: #820005; border-color: #820005; color: #fff; border-radius: 999px; box-shadow: 0 8px 22px rgba(130, 0, 5, .3); }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="login-header">
            <img src="{{ app_media_url('images/defaults/logo-aci.png', 'images/defaults/logo-aci.png') }}" alt="Logo ACI" class="login-logo" style="object-fit:contain;border-radius:0;background:transparent;padding:0;">
            <h1>ACI</h1>
            <p>Ingresa con tu usuario para acceder segun tu perfil.</p>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ \App\Support\AppUrl::route('login.store') }}">
                @csrf
                <div class="form-group">
                    <label>Usuario</label>
                    <input type="text" name="nombre_usuario" class="form-control" value="{{ old('nombre_usuario') }}" required autofocus>
                </div>
                <div class="form-group">
                    <label>Contrasena</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                    <label class="form-check-label" for="remember">Recordarme</label>
                </div>
                <button class="btn btn-primary btn-block" style="background:#820005;border-color:#820005;">Ingresar</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const successMessage = @json(session('status'));
            const validationErrors = @json($errors->all());

            if (successMessage) {
                Swal.fire({
                    icon: 'success',
                    title: 'Operacion realizada',
                    text: successMessage,
                    confirmButtonColor: '#820005'
                });
            }

            if (validationErrors.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'No fue posible ingresar',
                    html: '<ul style="text-align:left;padding-left:1.2rem;margin:0;">' +
                        validationErrors.map(function (error) {
                            return '<li>' + error + '</li>';
                        }).join('') +
                        '</ul>',
                    confirmButtonColor: '#d33'
                });
            }
        });
    </script>
    <x-pwa-install />
</body>
</html>
