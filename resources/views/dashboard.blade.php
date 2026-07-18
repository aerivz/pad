<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <style>
        body { font-family: 'Source Sans Pro', sans-serif; }
        #login-page { min-height: 100vh; background: linear-gradient(135deg, #16314f, #244d79 50%, #12253d); display: flex; align-items: center; justify-content: center; }
        .login-box-custom { width: 400px; }
        .brand-link { background: #001f3f !important; }
        .brand-text, .login-logo a { color: #fff !important; font-weight: 700 !important; }
        .brand-text span, .login-logo span { color: #820005; }
        .login-card, .card, .small-box { border-radius: 12px; }
        .login-card .card-body { padding: 32px; }
        .avatar-initials { width: 36px; height: 36px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; }
        .page-section { display: none; }
        .page-section.active { display: block; }
        .weight-pill { display: inline-block; margin: 2px; padding: 4px 10px; border-radius: 999px; background: #e9f2ff; color: #1e4f8f; font-size: .75rem; font-weight: 700; }
        .grade-input { width: 70px; margin: 0 auto; text-align: center; }
        .final-cell { background: #e8f7ec; font-weight: 700; text-align: center; }
        .score-badge { display: inline-block; min-width: 46px; text-align: center; padding: 3px 8px; border-radius: 6px; font-weight: 700; }
        .score-high { background: #d4edda; color: #155724; }
        .score-mid { background: #fff3cd; color: #856404; }
        .score-low { background: #f8d7da; color: #721c24; }
        .config-note { border: 1px dashed #c7d2e0; background: #f8fbff; border-radius: 10px; padding: 14px 16px; }
        .action-cell form { display: inline-block; margin: 0 2px; }
        .sticky-card { position: sticky; top: 1rem; }
    </style>
</head>
<body class="hold-transition">
@php
    $menu = [
        's-dashboard' => ['Dashboard', 'fas fa-tachometer-alt'],
        's-secciones' => ['Secciones', 'fas fa-school'],
        's-alumnos' => ['Alumnos', 'fas fa-user-graduate'],
        's-profesores' => ['Profesores', 'fas fa-chalkboard-teacher'],
        's-materias' => ['Materias', 'fas fa-book-open'],
        's-padres' => ['Padres', 'fas fa-users'],
        's-notas' => ['Notas', 'fas fa-pencil-alt'],
        's-reportcard' => ['Report Card', 'fas fa-clipboard-list'],
        's-correos' => ['Correos', 'fas fa-paper-plane'],
        's-usuarios' => ['Usuarios', 'fas fa-user-shield'],
        's-config' => ['ConfiguraciÃ³n', 'fas fa-cog'],
    ];
    $menuUrls = [
        's-dashboard' => '/pad/',
        's-secciones' => '/pad/secciones',
        's-alumnos' => '/pad/alumnos',
        's-profesores' => '/pad/profesores',
        's-materias' => '/pad/materias',
        's-padres' => '/pad/padres',
        's-notas' => '/pad/?tab=s-notas',
        's-reportcard' => '/pad/?tab=s-reportcard',
        's-correos' => '/pad/?tab=s-correos',
        's-usuarios' => '/pad/usuarios',
        's-config' => '/pad/?tab=s-config',
    ];
    $subjectColumns = ['Lenguaje y Literatura', 'MatemÃ¡tica', 'Ciencias Naturales', 'Estudios Sociales', 'InglÃ©s'];
    $scoreClass = fn ($score) => $score >= 85 ? 'score-high' : ($score >= 70 ? 'score-mid' : 'score-low');
@endphp
@php($dashboardBase = '/pad/')

<div id="login-page">
    <div class="login-box login-box-custom">
        <div class="login-logo text-center mb-3"><img src="{{ app_media_url('images/defaults/logo-aci.png', 'images/defaults/logo-aci.png') }}" alt="Logo ACI" style="width:54px;height:54px;object-fit:contain;margin-right:.75rem;"><a href="/pad/">A<span>CI</span></a></div>
        <div class="card login-card">
            <div class="card-body">
                <p class="login-box-msg text-muted mb-1">Bienvenido al sistema acadÃ©mico</p>
                <p class="text-center text-muted small mb-3">Laravel + MySQL generado desde tu maqueta</p>
                <div class="input-group mb-3">
                    <input type="email" class="form-control" value="admin@colegio.sv" readonly>
                    <div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" value="123456" readonly>
                    <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
                </div>
                <button class="btn btn-primary btn-block btn-lg" onclick="showApp()"><i class="fas fa-sign-in-alt mr-2"></i>Entrar al demo</button>
            </div>
        </div>
    </div>
</div>

<div id="app-layout" class="wrapper" style="display:none;">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li>
            <li class="nav-item d-none d-sm-inline-block"><a href="/pad/" class="nav-link text-muted small" id="nav-breadcrumb">Inicio / Dashboard</a></li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#"><span class="avatar-initials bg-primary mr-1">AD</span><span class="d-none d-md-inline text-sm">Admin Sistema</span></a>
                <div class="dropdown-menu dropdown-menu-right"><a href="#" class="dropdown-item" onclick="showLogin()"><i class="fas fa-sign-out-alt mr-2"></i>Volver al login</a></div>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-navy elevation-4">
        <a href="/pad/" class="brand-link">
            <img src="{{ app_media_url('images/defaults/logo-aci.png', 'images/defaults/logo-aci.png') }}" alt="Logo ACI" class="brand-image-logo" style="width:40px;height:40px;object-fit:contain;margin-left:8px;margin-right:.65rem;">
            <span class="brand-text">A<span>CI</span></span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image"><span class="avatar-initials bg-gradient-primary" style="width:34px;height:34px;font-size:.75rem;">AD</span></div>
                <div class="info"><a href="/pad/" class="d-block text-white">Administrador General</a><small class="text-light">Sistema acadÃ©mico 2025</small></div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column">
                    @foreach ($menu as $id => [$label, $icon])
                        <li class="nav-item">
                            <a href="{{ $menuUrls[$id] ?? '/pad/' }}" class="nav-link {{ $activeTab === $id || ($id === 's-dashboard' && $activeTab === 's-dashboard') ? 'active' : '' }}">
                                <i class="nav-icon {{ $icon }}"></i><p>{{ $label }}</p>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1 class="m-0" id="page-title">Dashboard</h1></div>
                    <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="/pad/">Inicio</a></li><li class="breadcrumb-item active" id="page-breadcrumb">Dashboard</li></ol></div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('status') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>RevisÃ¡ los datos ingresados.</strong>
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div id="s-dashboard" class="page-section active">
                    <div class="row">
                        <div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3>{{ $stats['alumnos'] }}</h3><p>Total alumnos</p></div><div class="icon"><i class="fas fa-user-graduate"></i></div></div></div>
                        <div class="col-lg-3 col-6"><div class="small-box bg-success"><div class="inner"><h3>{{ $stats['secciones'] }}</h3><p>Secciones activas</p></div><div class="icon"><i class="fas fa-school"></i></div></div></div>
                        <div class="col-lg-3 col-6"><div class="small-box bg-warning"><div class="inner"><h3>{{ $stats['profesores'] }}</h3><p>Profesores</p></div><div class="icon"><i class="fas fa-chalkboard-teacher"></i></div></div></div>
                        <div class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h3>{{ $stats['notas'] }}</h3><p>Notas cargadas</p></div><div class="icon"><i class="fas fa-pencil-alt"></i></div></div></div>
                    </div>
                    <div class="callout callout-warning">
                        <h5><i class="fas fa-exclamation-triangle mr-2"></i>Base inicial lista para crecer</h5>
                        <p>Ya estÃ¡ conectada la maqueta con datos reales desde Laravel y MySQL.</p>
                    </div>
                    <div class="row">
                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-header border-0"><h3 class="card-title"><i class="fas fa-chart-bar mr-2 text-primary"></i>Rendimiento por secciÃ³n</h3></div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover">
                                        <thead class="bg-light"><tr><th>SecciÃ³n</th><th>Alumnos</th><th>Materias</th><th>Promedio</th></tr></thead>
                                        <tbody>
                                        @foreach ($sections as $section)
                                            <tr>
                                                <td><strong>{{ $section->grado }} {{ $section->nombre }}</strong></td>
                                                <td><span class="badge badge-info">{{ $section->total_alumnos }}</span></td>
                                                <td><span class="badge badge-secondary">{{ $section->total_materias }}</span></td>
                                                <td><strong class="{{ ($section->promedio ?? 0) >= 80 ? 'text-success' : 'text-warning' }}">{{ $section->promedio ?? 'â€”' }}</strong></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="card">
                                <div class="card-header border-0"><h3 class="card-title"><i class="fas fa-clock mr-2 text-secondary"></i>Actividad reciente</h3></div>
                                <div class="card-body p-0">
                                    <ul class="products-list product-list-in-card pl-2 pr-2">
                                        @forelse ($audit as $item)
                                            <li class="item p-2">
                                                <div class="product-img"><i class="fas fa-history fa-lg mt-1 text-info"></i></div>
                                                <div class="product-info">
                                                    <span class="product-title text-sm">{{ $item->accion }} de nota por {{ $item->nombre_usuario }}</span>
                                                    <span class="product-description text-muted" style="font-size:.75rem;">Alumno #{{ $item->alumno_id }} | Nuevo valor: {{ $item->valor_nuevo ?? 'â€”' }}</span>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="p-3 text-muted small">TodavÃ­a no hay registros en auditorÃ­a.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="s-secciones" class="page-section">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card sticky-card">
                                <div class="card-header"><h3 class="card-title">{{ $editSection ? 'Editar secciÃ³n' : 'Nueva secciÃ³n' }}</h3></div>
                                <div class="card-body">
                                    <form method="POST" action="{{ $editSection ? '/pad/secciones/'.$editSection->id : '/pad/secciones' }}">
                                        @csrf
                                        @if ($editSection) @method('PATCH') @endif
                                        <div class="form-group"><label>Nombre</label><input name="nombre" class="form-control" value="{{ old('nombre', $editSection->nombre ?? '') }}" required></div>
                                        <div class="form-group"><label>Grado</label><input name="grado" class="form-control" value="{{ old('grado', $editSection->grado ?? '') }}" required></div>
                                        <div class="form-group"><label>AÃ±o escolar</label><input type="number" name="anio_escolar" class="form-control" value="{{ old('anio_escolar', $editSection->anio_escolar ?? date('Y')) }}" required></div>
                                        <button class="btn btn-primary btn-sm">{{ $editSection ? 'Guardar cambios' : 'Agregar secciÃ³n' }}</button>
                                        @if ($editSection)<a href="{{ $dashboardBase }}?tab=s-secciones" class="btn btn-default btn-sm">Cancelar</a>@endif
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header"><h3 class="card-title">Secciones registradas</h3></div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover">
                                        <thead class="bg-light"><tr><th>#</th><th>SecciÃ³n</th><th>AÃ±o</th><th>Alumnos</th><th>Materias</th><th>Promedio</th><th>Acciones</th></tr></thead>
                                        <tbody>
                                        @foreach ($sections as $section)
                                            <tr>
                                                <td>{{ $section->id }}</td>
                                                <td><strong>{{ $section->grado }} {{ $section->nombre }}</strong></td>
                                                <td>{{ $section->anio_escolar }}</td>
                                                <td>{{ $section->total_alumnos }}</td>
                                                <td>{{ $section->total_materias }}</td>
                                                <td>{{ $section->promedio ?? 'â€”' }}</td>
                                                <td class="action-cell">
                                                    <a href="{{ $dashboardBase }}?tab=s-secciones&edit_section={{ $section->id }}" class="btn btn-xs btn-warning">Editar</a>
                                                    <form method="POST" action="{{ '/pad/secciones/'.$section->id }}">@csrf @method('DELETE')<button class="btn btn-xs btn-danger" onclick="return confirm('Â¿Desactivar esta secciÃ³n?')">Desactivar</button></form>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="s-alumnos" class="page-section">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card sticky-card">
                                <div class="card-header"><h3 class="card-title">{{ $editStudent ? 'Editar alumno' : 'Nuevo alumno' }}</h3></div>
                                <div class="card-body">
                                    <form method="POST" action="{{ $editStudent ? '/pad/alumnos/'.$editStudent->id : '/pad/alumnos' }}">
                                        @csrf
                                        @if ($editStudent) @method('PATCH') @endif
                                        <div class="form-group"><label>SecciÃ³n</label><select name="seccion_id" class="form-control" required>@foreach ($sections as $section)<option value="{{ $section->id }}" @selected(old('seccion_id', $editStudent->seccion_id ?? '') == $section->id)>{{ $section->grado }} {{ $section->nombre }}</option>@endforeach</select></div>
                                        <div class="form-group"><label>Nombres</label><input name="nombres" class="form-control" value="{{ old('nombres', $editStudent->nombres ?? '') }}" required></div>
                                        <div class="form-group"><label>Apellidos</label><input name="apellidos" class="form-control" value="{{ old('apellidos', $editStudent->apellidos ?? '') }}" required></div>
                                        <button class="btn btn-primary btn-sm">{{ $editStudent ? 'Guardar cambios' : 'Agregar alumno' }}</button>
                                        @if ($editStudent)<a href="{{ $dashboardBase }}?tab=s-alumnos" class="btn btn-default btn-sm">Cancelar</a>@endif
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header"><h3 class="card-title">Lista de alumnos</h3></div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover">
                                        <thead class="bg-light"><tr><th>#</th><th>Estudiante</th><th>SecciÃ³n</th><th>Padres</th><th>Promedio</th><th>Acciones</th></tr></thead>
                                        <tbody>
                                        @foreach ($students as $student)
                                            <tr>
                                                <td>{{ $student->id }}</td>
                                                <td><strong>{{ $student->nombres }} {{ $student->apellidos }}</strong></td>
                                                <td><span class="badge badge-info">{{ $student->grado }} {{ $student->seccion_nombre }}</span></td>
                                                <td>{{ $student->total_padres }}</td>
                                                <td><strong class="{{ ($student->promedio ?? 0) >= 80 ? 'text-success' : 'text-warning' }}">{{ $student->promedio ?? 'â€”' }}</strong></td>
                                                <td class="action-cell">
                                                    <a href="{{ $dashboardBase }}?tab=s-alumnos&edit_student={{ $student->id }}" class="btn btn-xs btn-warning">Editar</a>
                                                    <form method="POST" action="{{ '/pad/alumnos/'.$student->id }}">@csrf @method('DELETE')<button class="btn btn-xs btn-danger" onclick="return confirm('Â¿Desactivar este alumno?')">Desactivar</button></form>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="s-profesores" class="page-section">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card sticky-card">
                                <div class="card-header"><h3 class="card-title">{{ $editTeacher ? 'Editar profesor' : 'Nuevo profesor' }}</h3></div>
                                <div class="card-body">
                                    <form method="POST" action="{{ $editTeacher ? '/pad/profesores/'.$editTeacher->id : '/pad/profesores' }}">
                                        @csrf
                                        @if ($editTeacher) @method('PATCH') @endif
                                        <div class="form-group"><label>Nombres</label><input name="nombres" class="form-control" value="{{ old('nombres', $editTeacher->nombres ?? '') }}" required></div>
                                        <div class="form-group"><label>Apellidos</label><input name="apellidos" class="form-control" value="{{ old('apellidos', $editTeacher->apellidos ?? '') }}" required></div>
                                        <div class="form-group"><label>Correo</label><input type="email" name="email" class="form-control" value="{{ old('email', $editTeacher->email ?? '') }}" required></div>
                                        <div class="form-group"><label>Especialidad</label><input name="especialidad" class="form-control" value="{{ old('especialidad', $editTeacher->especialidad ?? '') }}"></div>
                                        <button class="btn btn-primary btn-sm">{{ $editTeacher ? 'Guardar cambios' : 'Agregar profesor' }}</button>
                                        @if ($editTeacher)<a href="{{ $dashboardBase }}?tab=s-profesores" class="btn btn-default btn-sm">Cancelar</a>@endif
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="row">
                                @foreach ($teachers as $teacher)
                                    <div class="col-md-6 mb-3">
                                        <div class="card card-widget widget-user-2 shadow-sm">
                                            <div class="widget-user-header bg-info py-3">
                                                <div class="widget-user-image"><span class="avatar-initials" style="width:50px;height:50px;font-size:1rem;background:rgba(255,255,255,.3);">{{ strtoupper(substr($teacher->nombres, 0, 1).substr($teacher->apellidos, 0, 1)) }}</span></div>
                                                <h5 class="widget-user-username">{{ $teacher->nombres }} {{ $teacher->apellidos }}</h5>
                                                <h6 class="widget-user-desc">{{ $teacher->especialidad ?? 'Sin especialidad' }}</h6>
                                            </div>
                                            <div class="card-footer p-0">
                                                <ul class="nav flex-column">
                                                    <li class="nav-item"><span class="nav-link">Correo <span class="float-right small text-muted">{{ $teacher->email }}</span></span></li>
                                                    <li class="nav-item"><span class="nav-link">Secciones <span class="float-right badge badge-primary">{{ $teacher->total_secciones }}</span></span></li>
                                                    <li class="nav-item"><span class="nav-link">Asignaciones <span class="float-right badge badge-secondary">{{ $teacher->total_asignaciones }}</span></span></li>
                                                    <li class="nav-item"><span class="nav-link small text-muted">{{ $teacher->materias ?: 'AÃºn sin materias asignadas' }}</span></li>
                                                    <li class="nav-item text-center py-2">
                                                        <a href="{{ $dashboardBase }}?tab=s-profesores&edit_teacher={{ $teacher->id }}" class="btn btn-xs btn-warning">Editar</a>
                                                        <form method="POST" action="{{ '/pad/profesores/'.$teacher->id }}">@csrf @method('DELETE')<button class="btn btn-xs btn-danger" onclick="return confirm('Â¿Desactivar este profesor?')">Desactivar</button></form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div id="s-materias" class="page-section">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card sticky-card">
                                <div class="card-header"><h3 class="card-title">{{ $editSubject ? 'Editar materia' : 'Nueva materia' }}</h3></div>
                                <div class="card-body">
                                    <form method="POST" action="{{ $editSubject ? '/pad/materias/'.$editSubject->id : '/pad/materias' }}">
                                        @csrf
                                        @if ($editSubject) @method('PATCH') @endif
                                        <div class="form-group"><label>Nombre</label><input name="nombre" class="form-control" value="{{ old('nombre', $editSubject->nombre ?? '') }}" required></div>
                                        <button class="btn btn-primary btn-sm">{{ $editSubject ? 'Guardar cambios' : 'Agregar materia' }}</button>
                                        @if ($editSubject)<a href="{{ $dashboardBase }}?tab=s-materias" class="btn btn-default btn-sm">Cancelar</a>@endif
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header"><h3 class="card-title">CatÃ¡logo de materias</h3></div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover">
                                        <thead class="bg-light"><tr><th>#</th><th>Materia</th><th>Profesores</th><th>Secciones</th><th>Promedio</th><th>Acciones</th></tr></thead>
                                        <tbody>
                                        @foreach ($subjects as $subject)
                                            <tr>
                                                <td>{{ $subject->id }}</td>
                                                <td><strong>{{ $subject->nombre }}</strong></td>
                                                <td>{{ $subject->total_profesores }}</td>
                                                <td>{{ $subject->total_secciones }}</td>
                                                <td><strong class="{{ ($subject->promedio ?? 0) >= 80 ? 'text-success' : 'text-warning' }}">{{ $subject->promedio ?? 'â€”' }}</strong></td>
                                                <td class="action-cell">
                                                    <a href="{{ $dashboardBase }}?tab=s-materias&edit_subject={{ $subject->id }}" class="btn btn-xs btn-warning">Editar</a>
                                                    <form method="POST" action="{{ '/pad/materias/'.$subject->id }}">@csrf @method('DELETE')<button class="btn btn-xs btn-danger" onclick="return confirm('Â¿Desactivar esta materia?')">Desactivar</button></form>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="s-padres" class="page-section">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card sticky-card">
                                <div class="card-header"><h3 class="card-title">{{ $editGuardian ? 'Editar padre de familia' : 'Nuevo padre de familia' }}</h3></div>
                                <div class="card-body">
                                    <form method="POST" action="{{ $editGuardian ? '/pad/padres/'.$editGuardian->id : '/pad/padres' }}">
                                        @csrf
                                        @if ($editGuardian) @method('PATCH') @endif
                                        <div class="form-group"><label>Nombres</label><input name="nombres" class="form-control" value="{{ old('nombres', $editGuardian->nombres ?? '') }}" required></div>
                                        <div class="form-group"><label>Apellidos</label><input name="apellidos" class="form-control" value="{{ old('apellidos', $editGuardian->apellidos ?? '') }}" required></div>
                                        <div class="form-group"><label>Correo principal</label><input type="email" name="email_principal" class="form-control" value="{{ old('email_principal', $editGuardian->email_principal ?? '') }}" required></div>
                                        <button class="btn btn-primary btn-sm">{{ $editGuardian ? 'Guardar cambios' : 'Agregar padre' }}</button>
                                        @if ($editGuardian)<a href="{{ $dashboardBase }}?tab=s-padres" class="btn btn-default btn-sm">Cancelar</a>@endif
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header"><h3 class="card-title">Padres de familia</h3></div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover">
                                        <thead class="bg-light"><tr><th>#</th><th>Nombre</th><th>Correo</th><th>Hijos</th><th>Estado</th><th>Acciones</th></tr></thead>
                                        <tbody>
                                        @foreach ($parents as $parent)
                                            <tr>
                                                <td>{{ $parent->id }}</td>
                                                <td><strong>{{ $parent->nombres }} {{ $parent->apellidos }}</strong></td>
                                                <td>{{ $parent->email_principal }}</td>
                                                <td>{{ $parent->total_hijos }}</td>
                                                <td>{!! $parent->ultimo_envio_id ? '<span class="badge badge-success">Con historial</span>' : '<span class="badge badge-secondary">Sin envÃ­os</span>' !!}</td>
                                                <td class="action-cell">
                                                    <a href="{{ $dashboardBase }}?tab=s-padres&edit_guardian={{ $parent->id }}" class="btn btn-xs btn-warning">Editar</a>
                                                    <form method="POST" action="{{ '/pad/padres/'.$parent->id }}">@csrf @method('DELETE')<button class="btn btn-xs btn-danger" onclick="return confirm('Â¿Desactivar este padre de familia?')">Desactivar</button></form>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="s-notas" class="page-section">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Ingreso de notas</h3>
                            @if ($gradeBoard['assignment'])
                                <div class="card-tools text-muted small">{{ $gradeBoard['assignment']->materia }} | {{ $gradeBoard['assignment']->grado }} {{ $gradeBoard['assignment']->seccion }} | {{ $gradeBoard['assignment']->profesor }}</div>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                @foreach ($gradeBoard['categories'] as $category)
                                    <span class="weight-pill">{{ $category->nombre }} {{ rtrim(rtrim(number_format($category->porcentaje, 2), '0'), '.') }}%</span>
                                @endforeach
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="bg-light"><tr><th>#</th><th>Alumno</th>@foreach ($gradeBoard['categories'] as $category)<th class="text-center">{{ $category->nombre }}</th>@endforeach<th class="text-center">Final</th></tr></thead>
                                    <tbody>
                                    @forelse ($gradeBoard['rows'] as $row)
                                        <tr>
                                            <td>{{ $row['id'] }}</td>
                                            <td>{{ $row['nombre'] }}</td>
                                            @foreach ($gradeBoard['categories'] as $category)
                                                <td class="text-center"><input class="form-control form-control-sm grade-input" value="{{ $row['grades'][$category->id] ?? '' }}" readonly></td>
                                            @endforeach
                                            <td class="final-cell">{{ $row['final'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="{{ 3 + $gradeBoard['categories']->count() }}" class="text-center text-muted">No hay tablero de notas disponible.</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="s-reportcard" class="page-section">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Report card consolidado</h3></div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Alumno</th>
                                        @foreach ($subjectColumns as $column)
                                            <th class="text-center">{{ $column }}</th>
                                        @endforeach
                                        <th class="text-center">Promedio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($reportCard as $report)
                                    <tr>
                                        <td><strong>{{ $report['alumno'] }}</strong></td>
                                        @foreach ($subjectColumns as $column)
                                            @php($value = $report['materias'][$column] ?? null)
                                            <td class="text-center">
                                                @if ($value !== null)
                                                    <span class="score-badge {{ $scoreClass($value) }}">{{ $value }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="text-center"><span class="score-badge {{ $scoreClass($report['promedio']) }}">{{ $report['promedio'] }}</span></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="s-correos" class="page-section">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">EnvÃ­o de reportes</h3></div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover">
                                <thead class="bg-light"><tr><th>#</th><th>Padre</th><th>Alumno</th><th>Plantilla</th><th>Trimestre</th><th>Estado</th></tr></thead>
                                <tbody>
                                @foreach ($emails as $email)
                                    <tr>
                                        <td>{{ $email->id }}</td>
                                        <td><strong>{{ $email->nombres }} {{ $email->apellidos }}</strong><br><small>{{ $email->email_principal }}</small></td>
                                        <td>{{ $email->alumno_nombres }} {{ $email->alumno_apellidos }}</td>
                                        <td>{{ $email->plantilla }}</td>
                                        <td>{{ $email->trimestre }}</td>
                                        <td>
                                            @if ($email->estado === 'enviado')
                                                <span class="badge badge-success">Enviado</span>
                                            @elseif ($email->estado === 'pendiente')
                                                <span class="badge badge-warning">Pendiente</span>
                                            @else
                                                <span class="badge badge-danger">Fallido</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="s-usuarios" class="page-section">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card sticky-card">
                                <div class="card-header"><h3 class="card-title">{{ $editUser ? 'Editar usuario' : 'Nuevo usuario' }}</h3></div>
                                <div class="card-body">
                                    <form method="POST" action="{{ $editUser ? '/pad/usuarios/'.$editUser->id : '/pad/usuarios' }}">
                                        @csrf
                                        @if ($editUser) @method('PATCH') @endif
                                        <div class="form-group"><label>Rol</label><select name="rol_id" class="form-control" required>@foreach ($roles as $role)<option value="{{ $role->id }}" @selected(old('rol_id', $editUser->rol_id ?? '') == $role->id)>{{ ucfirst($role->nombre) }}</option>@endforeach</select></div>
                                        <div class="form-group"><label>Usuario</label><input name="nombre_usuario" class="form-control" value="{{ old('nombre_usuario', $editUser->nombre_usuario ?? '') }}" required></div>
                                        <div class="form-group"><label>Nombres</label><input name="nombres" class="form-control" value="{{ old('nombres', $editUser->nombres ?? '') }}" required></div>
                                        <div class="form-group"><label>Apellidos</label><input name="apellidos" class="form-control" value="{{ old('apellidos', $editUser->apellidos ?? '') }}" required></div>
                                        <div class="form-group"><label>Correo</label><input type="email" name="email" class="form-control" value="{{ old('email', $editUser->email ?? '') }}" required></div>
                                        <div class="form-group"><label>{{ $editUser ? 'Nueva contraseÃ±a (opcional)' : 'ContraseÃ±a' }}</label><input type="password" name="password" class="form-control" {{ $editUser ? '' : 'required' }}></div>
                                        <button class="btn btn-primary btn-sm">{{ $editUser ? 'Guardar cambios' : 'Agregar usuario' }}</button>
                                        @if ($editUser)<a href="{{ $dashboardBase }}?tab=s-usuarios" class="btn btn-default btn-sm">Cancelar</a>@endif
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header"><h3 class="card-title">Usuarios del sistema</h3></div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover">
                                        <thead class="bg-light"><tr><th>#</th><th>Usuario</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Acciones</th></tr></thead>
                                        <tbody>
                                        @foreach ($users as $user)
                                            <tr>
                                                <td>{{ $user->id }}</td>
                                                <td><strong>{{ $user->nombre_usuario }}</strong></td>
                                                <td>{{ $user->nombres }} {{ $user->apellidos }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td><span class="badge badge-info text-uppercase">{{ $user->rol }}</span></td>
                                                <td class="action-cell">
                                                    <a href="{{ $dashboardBase }}?tab=s-usuarios&edit_user={{ $user->id }}" class="btn btn-xs btn-warning">Editar</a>
                                                    <form method="POST" action="{{ '/pad/usuarios/'.$user->id }}">@csrf @method('DELETE')<button class="btn btn-xs btn-danger" onclick="return confirm('Â¿Desactivar este usuario?')">Desactivar</button></form>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="s-config" class="page-section">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header"><h3 class="card-title">Resumen tÃ©cnico</h3></div>
                                <div class="card-body">
                                    <div class="config-note mb-3">La interfaz se montÃ³ con base en tu HTML y la estructura MySQL se tradujo a migraciones y seeders.</div>
                                    <ul class="mb-0 pl-3">
                                        <li>Base objetivo: <strong>pad</strong>.</li>
                                        <li>Tablas implementadas: roles, usuarios, profesores, secciones, alumnos, materias, trimestres, categorÃ­as, asignaciones, notas, padres, plantillas, envÃ­os y auditorÃ­a.</li>
                                        <li>Los mÃ³dulos <strong>menus</strong>, <strong>modulos</strong> y <strong>configuracion</strong> no aparecen creados en tu script final, asÃ­ que esta pantalla quedÃ³ informativa.</li>
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
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
<script>
    const initialTab = @json($activeTab);
    const menuTitles = @json(collect($menu)->mapWithKeys(fn ($item, $key) => [$key => $item[0]]));

    function showApp() {
        $('#login-page').hide();
        $('#app-layout').show();
        $('body').addClass('sidebar-mini');
    }
    function showLogin() {
        $('#app-layout').hide();
        $('#login-page').show();
        $('body').removeClass('sidebar-mini');
    }
    function showSection(id, el, title) {
        $('.page-section').removeClass('active');
        $('#' + id).addClass('active');
        $('.nav-sidebar .nav-link').removeClass('active');
        if (el) $(el).addClass('active');
        $('#page-title').text(title);
        $('#page-breadcrumb').text(title);
        $('#nav-breadcrumb').text('Inicio / ' + title);
        return false;
    }

    $(function () {
        const shouldOpenApp = initialTab !== 's-dashboard' || {{ $errors->any() ? 'true' : 'false' }} || {{ session()->has('status') ? 'true' : 'false' }};
        if (shouldOpenApp) {
            showApp();
        }

        if (initialTab) {
            const $link = $('.nav-sidebar .nav-link').filter(function () {
                return $(this).attr('href') === {{ Illuminate\Support\Js::from($menuUrls) }}[initialTab];
            }).first();
            showSection(initialTab, $link.length ? $link[0] : null, menuTitles[initialTab] || 'Dashboard');
        }
    });
</script>
</body>
</html>

