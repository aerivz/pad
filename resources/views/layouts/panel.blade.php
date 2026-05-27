<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduNotas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    @stack('styles')
    <style>
        body { font-family: 'Source Sans Pro', sans-serif; }
        .brand-link { background: #001f3f !important; }
        .brand-text { color: #fff !important; font-weight: 700 !important; }
        .brand-text span { color: #74b9ff; }
        .card, .small-box { border-radius: 12px; }
        .sticky-card { position: sticky; top: 1rem; }
        .avatar-initials { width: 36px; height: 36px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; }
        .score-badge { display: inline-block; min-width: 46px; text-align: center; padding: 3px 8px; border-radius: 6px; font-weight: 700; }
        .score-high { background: #d4edda; color: #155724; }
        .score-mid { background: #fff3cd; color: #856404; }
        .score-low { background: #f8d7da; color: #721c24; }
        .weight-pill { display: inline-block; margin: 2px; padding: 4px 10px; border-radius: 999px; background: #e9f2ff; color: #1e4f8f; font-size: .75rem; font-weight: 700; }
        .final-cell { background: #e8f7ec; font-weight: 700; text-align: center; }
        .grade-input { width: 70px; margin: 0 auto; text-align: center; }
        .config-note { border: 1px dashed #c7d2e0; background: #f8fbff; border-radius: 10px; padding: 14px 16px; }
        .action-cell form { display: inline-block; margin: 0 2px; }
        .filter-toolbar { display: flex; flex-wrap: wrap; gap: .75rem; align-items: end; }
        .filter-toolbar .form-group { margin-bottom: 0; min-width: 180px; }
        .swal2-popup { font-family: 'Source Sans Pro', sans-serif; }
        .brand-image-logo { width: 32px; height: 32px; object-fit: cover; border-radius: 8px; margin-left: 8px; margin-right: .5rem; }
        .user-avatar-image { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
        .user-avatar-sidebar { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; }
        .action-cell .btn { margin-bottom: .25rem; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li>
            <li class="nav-item d-none d-sm-inline-block"><a href="{{ app_nav_url() }}" class="nav-link text-muted small">Inicio / {{ $menu[$activeMenu]['label'] ?? 'Dashboard' }}</a></li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                @php($user = auth()->user())
                @php($initials = strtoupper(substr($user->nombres ?? 'A', 0, 1).substr($user->apellidos ?? 'D', 0, 1)))
                @php($userAvatarUrl = $user?->avatar_url ?? app_media_url(null, 'images/defaults/avatar.svg'))
                <a class="nav-link" data-toggle="dropdown" href="#"><img src="{{ $userAvatarUrl }}" alt="Avatar" class="user-avatar-image mr-1"><span class="d-none d-md-inline text-sm">{{ trim(($user->nombres ?? '').' '.($user->apellidos ?? '')) ?: 'Usuario' }}</span></a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="{{ app_nav_url() }}" class="dropdown-item"><i class="fas fa-home mr-2"></i>Ir al dashboard</a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ \App\Support\AppUrl::route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt mr-2"></i>Cerrar sesion</button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-navy elevation-4">
        <a href="{{ app_nav_url() }}" class="brand-link">
            <img src="{{ app_media_url('images/defaults/logo.svg', 'images/defaults/logo.svg') }}" alt="Logo" class="brand-image-logo">
            <span class="brand-text">Edu<span>Notas</span></span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image"><img src="{{ $userAvatarUrl }}" alt="Avatar" class="user-avatar-sidebar"></div>
                <div class="info">
                    <a href="{{ app_nav_url() }}" class="d-block text-white">{{ trim(($user->nombres ?? '').' '.($user->apellidos ?? '')) ?: 'Usuario' }}</a>
                    <small class="text-light text-capitalize">{{ $user->role->nombre ?? 'Sin perfil' }}</small>
                </div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    @foreach ($menu as $key => $item)
                        @php($children = $item['children'] ?? [])
                        @php($isChildActive = collect($children)->contains(fn ($child) => $activeMenu === $child['key']))
                        <li class="nav-item {{ count($children) > 0 ? 'has-treeview '.(($activeMenu === $key || $isChildActive) ? 'menu-open' : '') : '' }}">
                            <a href="{{ count($children) > 0 ? ($item['url'] === '#' ? '#' : $item['url']) : $item['url'] }}" class="nav-link {{ $activeMenu === $key || $isChildActive ? 'active' : '' }} {{ count($children) > 0 ? 'submenu-toggle' : '' }}" @if(count($children) > 0) data-submenu-toggle="true" @endif>
                                <i class="nav-icon {{ $item['icon'] }}"></i>
                                <p>
                                    {{ $item['label'] }}
                                    @if (count($children) > 0)
                                        <i class="right fas fa-angle-left"></i>
                                    @endif
                                </p>
                            </a>
                            @if (count($children) > 0)
                                <ul class="nav nav-treeview">
                                    @foreach ($children as $child)
                                        <li class="nav-item">
                                            <a href="{{ $child['url'] }}" class="nav-link {{ $activeMenu === $child['key'] ? 'active' : '' }}">
                                                <i class="nav-icon {{ $child['icon'] ?: 'far fa-circle' }}"></i>
                                                <p>{{ $child['label'] }}</p>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
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
                    <div class="col-sm-6"><h1 class="m-0">@yield('title')</h1></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ app_nav_url() }}">Inicio</a></li>
                            <li class="breadcrumb-item active">@yield('title')</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stack('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const successMessage = @json(session('status'));
        const validationErrors = @json($errors->all());

        if (successMessage) {
            Swal.fire({
                icon: 'success',
                title: 'Operacion realizada',
                text: successMessage,
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#1f6feb'
            });
        }

        if (validationErrors.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Revisa los datos ingresados',
                html: '<ul style="text-align:left;padding-left:1.2rem;margin:0;">' +
                    validationErrors.map(function (error) {
                        return '<li>' + error + '</li>';
                    }).join('') +
                    '</ul>',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#d33'
            });
        }

        document.querySelectorAll('form[data-swal-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (form.dataset.confirmed === 'true') {
                    return;
                }

                event.preventDefault();

                Swal.fire({
                    icon: 'warning',
                    title: form.dataset.swalTitle || 'Confirmar accion',
                    text: form.dataset.swalText || 'Esta accion cambiara el estado del registro.',
                    showCancelButton: true,
                    confirmButtonText: form.dataset.swalConfirmLabel || 'Si, continuar',
                    cancelButtonText: form.dataset.swalCancel || 'Cancelar',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }
                });
            });
        });

        document.querySelectorAll('[data-filter-target]').forEach(function (toolbar) {
            const tableId = toolbar.dataset.filterTarget;
            const table = document.getElementById(tableId);

            if (!table) {
                return;
            }

            const rows = Array.from(table.querySelectorAll('tbody tr[data-filter-row]'));
            const emptyRow = table.querySelector('tbody tr[data-empty-filter]');
            const filters = Array.from(toolbar.querySelectorAll('[data-filter-name]'));

            const applyFilters = function () {
                let visibleRows = 0;

                rows.forEach(function (row) {
                    const matches = filters.every(function (filter) {
                        const filterName = filter.dataset.filterName;
                        const filterValue = (filter.value || '').toString().trim().toLowerCase();

                        if (!filterValue) {
                            return true;
                        }

                        if (filter.tagName === 'SELECT') {
                            return (row.dataset[filterName] || '').toLowerCase() === filterValue;
                        }

                        return (row.dataset[filterName] || '').toLowerCase().includes(filterValue);
                    });

                    row.style.display = matches ? '' : 'none';
                    if (matches) {
                        visibleRows += 1;
                    }
                });

                if (emptyRow) {
                    emptyRow.style.display = visibleRows === 0 ? '' : 'none';
                }
            };

            filters.forEach(function (filter) {
                filter.addEventListener('input', applyFilters);
                filter.addEventListener('change', applyFilters);
            });

            applyFilters();
        });

        document.querySelectorAll('.submenu-toggle[data-submenu-toggle="true"]').forEach(function (toggle) {
            toggle.addEventListener('click', function (event) {
                if (window.innerWidth >= 992) {
                    return;
                }

                event.preventDefault();

                const item = toggle.closest('.nav-item');

                if (!item) {
                    return;
                }

                item.classList.toggle('menu-open');
                toggle.classList.toggle('active');
            });
        });
    });
</script>
</body>
</html>
