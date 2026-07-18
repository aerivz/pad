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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    @stack('styles')
    <style>
        body { font-family: 'Source Sans Pro', sans-serif; }
        .main-sidebar.sidebar-dark-navy {
            background: #ffffff !important;
            border-right: 1px solid #e5e7eb;
            box-shadow: 10px 0 28px rgba(15, 23, 42, .05);
        }
        .brand-link {
            background: #ffffff !important;
            border-bottom: 1px solid #eef2f7;
        }
        .brand-text { color: #111827 !important; font-weight: 700 !important; }
        .brand-text span { color: #820005; }
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
        .brand-image-logo { width: 40px; height: 40px; object-fit: contain; border-radius: 0; margin-left: 8px; margin-right: .65rem; }
        .user-avatar-image { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
        .user-avatar-sidebar { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; }
        .action-cell .btn { margin-bottom: .25rem; }
        .maint-card { border: 1px solid #d9e2f2; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
        .maint-toolbar { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 1rem; align-items: center; }
        .maint-toolbar-title { display: flex; align-items: center; gap: .65rem; font-weight: 700; color: #1f2d3d; }
        .maint-toolbar-title i { color: #820005; }
        .maint-actions { display: flex; flex-wrap: wrap; gap: .5rem; }
        .maint-search-grid { display: grid; grid-template-columns: minmax(220px, 2fr) minmax(180px, 1fr) auto; gap: 1rem; align-items: end; }
        .maint-tags { display: flex; flex-wrap: wrap; gap: .65rem; }
        .maint-tag { background: #f1f5f9; border-radius: 999px; padding: .45rem .85rem; color: #475569; font-size: .84rem; font-weight: 600; }
        .maint-card .table-responsive { border: 1px solid #dbe3ee; border-radius: 14px; overflow: hidden; background: #fff; }
        .maint-table { margin-bottom: 0; border-collapse: separate; border-spacing: 0; }
        .maint-table thead th { background: #f8fafc; font-size: .84rem; text-transform: none; letter-spacing: .01em; color: #52627a; border-top: 0; border-bottom: 1px solid #dbe3ee; padding: .95rem .9rem; font-weight: 700; }
        .maint-table thead th:first-child { border-top-left-radius: 14px; }
        .maint-table thead th:last-child { border-top-right-radius: 14px; }
        .maint-table tbody td { vertical-align: middle; padding: .9rem .9rem; border-top: 0; border-bottom: 1px solid #e7edf5; color: #243447; background: #fff; }
        .maint-table tbody tr:last-child td { border-bottom: 0; }
        .maint-table tbody tr:hover td { background: #fbfdff; }
        .maint-avatar { width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: #f7e8ea; color: #820005; font-weight: 700; margin-right: .5rem; }
        .maint-identity { display: flex; align-items: center; }
        .maint-status { display: inline-flex; align-items: center; justify-content: center; min-width: 72px; padding: .15rem .55rem; border-radius: 999px; font-size: .74rem; font-weight: 700; }
        .maint-status-active { background: #dcfce7; color: #166534; }
        .maint-status-muted { background: #e2e8f0; color: #475569; }
        .maint-actions-cell { white-space: nowrap; }
        .maint-actions-cell form { display: inline-block; }
        .maint-actions-cell .btn { border-radius: .5rem; }
        .maint-form-card { border: 1px dashed #bfd2f0; background: linear-gradient(180deg, #f8fbff, #ffffff); }
        .maint-form-card .card-header { background: transparent; }
        .maint-modal-list { margin: 0; padding-left: 1rem; }
        .sidebar-dark-navy .brand-link .brand-image-logo {
            background: #f8fafc;
            padding: .2rem;
            border: 1px solid #e5e7eb;
        }
        .sidebar-dark-navy .user-panel {
            border-bottom: 1px solid #eef2f7 !important;
        }
        .sidebar-dark-navy .user-panel .info a,
        .sidebar-dark-navy .user-panel small {
            color: #374151 !important;
        }
        .sidebar-dark-navy .nav-sidebar > .nav-item {
            margin: .15rem .6rem;
        }
        .sidebar-dark-navy .nav-sidebar > .nav-item.menu-group {
            margin-top: .75rem;
        }
        .sidebar-dark-navy .nav-sidebar > .nav-item.menu-dashboard {
            margin-bottom: .7rem;
            padding-bottom: .7rem;
            border-bottom: 1px solid #eef2f7;
        }
        .sidebar-dark-navy .nav-sidebar > .nav-item > .nav-link,
        .sidebar-dark-navy .nav-treeview > .nav-item > .nav-link {
            color: #4b5563 !important;
            border-radius: 12px;
            font-weight: 600;
            padding-top: .8rem;
            padding-bottom: .8rem;
            transition: background .2s ease, color .2s ease, transform .2s ease;
        }
        .sidebar-dark-navy .nav-sidebar > .nav-item > .nav-link:hover,
        .sidebar-dark-navy .nav-treeview > .nav-item > .nav-link:hover {
            background: #f8fafc;
            color: #111827 !important;
        }
        .sidebar-dark-navy .nav-sidebar > .nav-item > .nav-link.active,
        .sidebar-dark-navy .nav-treeview > .nav-item > .nav-link.active {
            background: #eef6ff;
            color: #820005 !important;
            box-shadow: inset 0 0 0 1px #ead2d7;
        }
        .sidebar-dark-navy .nav-sidebar > .nav-item.menu-group > .nav-link .right {
            color: #9ca3af !important;
            transition: transform .2s ease;
        }
        .sidebar-dark-navy .nav-sidebar > .nav-item.menu-group.menu-open > .nav-link .right {
            transform: rotate(-90deg);
            color: #820005 !important;
        }
        .sidebar-dark-navy .nav-sidebar > .nav-item.menu-dashboard > .nav-link {
            background: linear-gradient(135deg, #ffffff 0%, #fbf2f3 100%);
            border: 1px solid #ead2d7;
            box-shadow: 0 8px 18px rgba(130, 0, 5, .06);
        }
        .sidebar-dark-navy .nav-sidebar > .nav-item > .nav-link.active .nav-icon,
        .sidebar-dark-navy .nav-treeview > .nav-item > .nav-link.active .nav-icon,
        .sidebar-dark-navy .nav-sidebar > .nav-item > .nav-link:hover .nav-icon,
        .sidebar-dark-navy .nav-treeview > .nav-item > .nav-link:hover .nav-icon {
            color: inherit !important;
        }
        .sidebar-dark-navy .nav-treeview {
            margin-top: .5rem;
            padding: .35rem 0 .25rem .9rem;
            border-left: 2px solid #ebeef3;
            margin-left: 1.35rem;
        }
        .sidebar-dark-navy .nav-treeview > .nav-item {
            margin: .15rem 0;
        }
        .sidebar-dark-navy .nav-treeview > .nav-item > .nav-link {
            padding-top: .65rem;
            padding-bottom: .65rem;
            padding-left: .85rem;
            font-size: .95rem;
        }
        .sidebar-dark-navy .nav-sidebar .nav-icon {
            color: #6b7280 !important;
        }
        .sidebar-dark-navy .nav-treeview > .nav-item > .nav-link .nav-icon {
            font-size: .85rem;
        }
        @media (max-width: 991.98px) {
            .maint-search-grid { grid-template-columns: 1fr; }
        }
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
            <img src="{{ app_media_url('images/defaults/logo-aci.png', 'images/defaults/logo-aci.png') }}" alt="Logo ACI" class="brand-image-logo">
            <span class="brand-text">A<span>CI</span></span>
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
                        @php($itemClasses = ['nav-item'])
                        @if ($key === 'dashboard')
                            @php($itemClasses[] = 'menu-dashboard')
                        @endif
                        @if (count($children) > 0)
                            @php($itemClasses[] = 'has-treeview')
                            @php($itemClasses[] = 'menu-group')
                            @if ($activeMenu === $key || $isChildActive)
                                @php($itemClasses[] = 'menu-open')
                            @endif
                        @endif
                        <li class="{{ implode(' ', $itemClasses) }}">
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

            const submitButton = toolbar.querySelector('[data-filter-submit]');
            const resetButton = toolbar.querySelector('[data-filter-reset]');

            if (submitButton) {
                submitButton.addEventListener('click', applyFilters);
            }

            if (resetButton) {
                resetButton.addEventListener('click', function () {
                    filters.forEach(function (filter) {
                        filter.value = '';
                    });

                    applyFilters();
                });
            }

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
