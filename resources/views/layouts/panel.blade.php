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
    @include('components.pwa-meta')
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
        .user-avatar-initials { display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background: #f7e8ea; color: #820005; font-weight: 700; }
        .user-avatar-top { width: 36px; height: 36px; margin-right: .25rem; }
        .user-avatar-side { width: 34px; height: 34px; font-size: .8rem; }
        .action-cell .btn { margin-bottom: .25rem; }
        .maint-card { border: 1px solid #d9e2f2; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
        .maint-toolbar { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 1rem; align-items: center; }
        .maint-toolbar-title { display: flex; align-items: center; gap: .65rem; font-weight: 700; color: #1f2d3d; }
        .maint-toolbar-title i { color: #820005; }
        .maint-actions { display: flex; flex-wrap: wrap; gap: .5rem; }
        .maint-search-grid { display: grid; grid-template-columns: minmax(220px, 2fr) minmax(180px, 1fr) auto; gap: 1rem; align-items: end; }
        .maint-tags { display: flex; flex-wrap: wrap; gap: .65rem; }
        .maint-tag { background: #f1f5f9; border-radius: 999px; padding: .45rem .85rem; color: #475569; font-size: .84rem; font-weight: 600; }
        .maint-card .table-responsive { border: 1px solid #dbe3ee; border-radius: 14px; overflow-x: auto; overflow-y: hidden; background: #fff; -webkit-overflow-scrolling: touch; }
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
        .pwa-install-button { position: fixed; right: 1rem; bottom: 1rem; z-index: 1080; background: #820005; border-color: #820005; color: #fff; border-radius: 999px; box-shadow: 0 8px 22px rgba(130, 0, 5, .3); }
        .pwa-install-button:hover { background: #650004; color: #fff; }
        .topbar-search { position: relative; width: min(320px, 32vw); margin-left: 1rem; }
        .topbar-search .form-control { height: 34px; padding-left: 2.2rem; border-radius: .6rem; border-color: #e5e7eb; background: #f8fafc; font-size: .86rem; }
        .topbar-search > i { position: absolute; left: .78rem; top: .61rem; z-index: 2; color: #8793a5; font-size: .76rem; }
        .menu-search-results { display: none; position: absolute; z-index: 1100; top: calc(100% + .45rem); width: 100%; max-height: 300px; overflow-y: auto; padding: .4rem; border: 1px solid #e5e7eb; border-radius: .75rem; background: #fff; box-shadow: 0 14px 30px rgba(15, 23, 42, .14); }
        .menu-search-results.show { display: block; }
        .menu-search-result { display: flex; gap: .65rem; align-items: center; padding: .65rem; border-radius: .5rem; color: #334155; font-size: .87rem; font-weight: 600; }
        .menu-search-result:hover { background: #fbeaec; color: #820005; text-decoration: none; }
        .menu-search-empty { padding: .75rem; color: #94a3b8; font-size: .84rem; text-align: center; }
        .header-icon-button { position: relative; display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; padding: 0 !important; border: 0 !important; border-radius: 10px; background: transparent !important; box-shadow: none !important; color: #52627a !important; outline: 0 !important; }
        .header-icon-button:hover { background: #f4f6f9 !important; color: #820005 !important; }
        #themeToggle { background: #f3f8fc !important; color: #168fbd !important; }
        #themeToggle:hover, #themeToggle:focus { background: #e8f4fa !important; color: #087eaa !important; }
        .header-icon-badge { position: absolute; top: 2px; right: 1px; min-width: 16px; height: 16px; padding: 0 4px; border-radius: 999px; background: #d63045; color: #fff; font-size: .62rem; line-height: 16px; font-weight: 700; border: 2px solid #fff; }
        .notification-dropdown { width: min(360px, calc(100vw - 1.25rem)); max-height: 420px; overflow-y: auto; padding: 0; border: 1px solid #e5e7eb; border-radius: .8rem; box-shadow: 0 16px 36px rgba(15, 23, 42, .16); }
        .notification-title { padding: .8rem 1rem; border-bottom: 1px solid #edf0f4; color: #1f2937; font-weight: 700; font-size: .9rem; }
        .notification-item { display: flex; align-items: flex-start; gap: .65rem; padding: .8rem 1rem; border-bottom: 1px solid #f0f2f5; }
        .notification-item:last-child { border-bottom: 0; }
        .notification-item.unread { background: #fff8f8; }
        .notification-mark { flex: 0 0 30px; width: 30px; height: 30px; border: 0; border-radius: 50%; background: #f7e8ea; color: #820005; }
        .notification-content { min-width: 0; flex: 1; color: #334155; }
        .notification-content strong { display: block; color: #253248; font-size: .82rem; }
        .notification-content small { display: block; margin-top: .15rem; color: #718096; font-size: .75rem; line-height: 1.3; }
        .notification-content:hover { text-decoration: none; }
        .header-user-toggle { display: inline-flex !important; align-items: center; gap: .5rem; margin-left: .2rem; padding: .25rem .55rem !important; border-radius: .8rem; color: #52627a !important; }
        .header-user-toggle:hover { background: #f5f8fb; color: #253248 !important; }
        .header-user-toggle .user-avatar-image, .header-user-toggle .user-avatar-top { margin-right: 0; width: 34px; height: 34px; }
        .header-user-toggle .header-user-name { max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: .84rem; font-weight: 700; }
        .header-user-dropdown { min-width: 250px; padding: .45rem; border: 1px solid #e6ebf2; border-radius: 1rem; box-shadow: 0 16px 34px rgba(15, 23, 42, .14); }
        .header-user-dropdown .dropdown-item { display: flex; align-items: center; gap: .55rem; padding: .8rem .9rem; border-radius: .65rem; color: #52627a; font-size: .9rem; font-weight: 600; }
        .header-user-dropdown .dropdown-item i { width: 18px; color: #168fbd; text-align: center; }
        .header-user-dropdown .dropdown-item:hover { background: #f2f8fc; color: #1f3b54; }
        .header-user-dropdown .dropdown-divider { margin: .35rem .2rem; }
        .header-user-dropdown form { margin: 0; }
        .header-user-dropdown button { width: 100%; border: 0; background: transparent; text-align: left; }
        /* Dark surfaces share the same navy scale so cards, tables and forms remain readable. */
        body.dark-theme { background: #101827; color: #dbe4f0; color-scheme: dark; }
        body.dark-theme .content-wrapper, body.dark-theme .main-footer { background: #101827; color: #dbe4f0; }
        body.dark-theme .main-header, body.dark-theme .brand-link, body.dark-theme .card, body.dark-theme .modal-content, body.dark-theme .dropdown-menu, body.dark-theme .menu-search-results, body.dark-theme .notification-dropdown { background: #182338 !important; color: #dbe4f0; border-color: #2f405b !important; }
        body.dark-theme .main-header { border-bottom-color: #2f405b !important; box-shadow: 0 1px 0 rgba(148, 163, 184, .08); }
        body.dark-theme .main-sidebar.sidebar-dark-navy { background: #182338 !important; border-color: #2f405b; }
        body.dark-theme .brand-text, body.dark-theme .profile-identity h1 { color: #f8fafc !important; }
        body.dark-theme .content-header h1, body.dark-theme .card-header, body.dark-theme .card-title, body.dark-theme .maint-toolbar-title, body.dark-theme label, body.dark-theme .table { color: #e5edf7 !important; }
        body.dark-theme .card-header, body.dark-theme .card-footer, body.dark-theme .notification-title, body.dark-theme .notification-item, body.dark-theme hr { border-color: #2f405b !important; }
        body.dark-theme .card-header, body.dark-theme .card-footer { background: #1d2a40 !important; }
        body.dark-theme .small-box { background: #182338 !important; color: #e8f0fa !important; border: 1px solid #2f405b; box-shadow: 0 12px 26px rgba(0, 0, 0, .2); }
        body.dark-theme .small-box .small-box-footer { background: rgba(7, 14, 27, .35); color: #b9c9de !important; }
        body.dark-theme .small-box .icon { color: rgba(116, 185, 255, .32); }
        body.dark-theme .maint-card .table-responsive, body.dark-theme .maint-table tbody td, body.dark-theme .maint-table thead th, body.dark-theme .table thead th { background: #182338 !important; color: #dbe4f0 !important; border-color: #2f405b !important; }
        body.dark-theme .table-striped tbody tr:nth-of-type(odd), body.dark-theme .table-hover tbody tr:hover { background-color: transparent; }
        body.dark-theme .table-striped tbody tr:nth-of-type(odd) td { background: #1b2940 !important; }
        body.dark-theme .maint-table tbody tr:hover td, body.dark-theme .table-hover tbody tr:hover td { background: #24344d !important; }
        body.dark-theme input.form-control, body.dark-theme select.form-control, body.dark-theme textarea.form-control, body.dark-theme .topbar-search .form-control { background: #101827; color: #e5edf7; border-color: #40526d; }
        body.dark-theme input.form-control::placeholder, body.dark-theme textarea.form-control::placeholder { color: #8292a8; }
        body.dark-theme select.form-control option { background: #182338; color: #e5edf7; }
        body.dark-theme .text-muted, body.dark-theme .small.text-muted, body.dark-theme .notification-content small { color: #9cadc2 !important; }
        body.dark-theme .header-icon-button { color: #cbd5e1 !important; }
        body.dark-theme .header-icon-button:hover { background: #253146; color: #fff !important; }
        body.dark-theme .header-icon-button:focus,
        body.dark-theme .header-icon-button:active,
        body.dark-theme .nav-item.show > .header-icon-button {
            background: #253146 !important;
            color: #fff !important;
            box-shadow: none !important;
            outline: 0 !important;
        }
        body.dark-theme .main-header.navbar .navbar-nav .header-icon-button:not(#themeToggle) { background: transparent !important; color: #cbd5e1 !important; }
        body.dark-theme .main-header.navbar .navbar-nav .header-icon-button:not(#themeToggle):hover,
        body.dark-theme .main-header.navbar .navbar-nav .header-icon-button:not(#themeToggle):focus,
        body.dark-theme .main-header.navbar .navbar-nav .header-icon-button:not(#themeToggle):active,
        body.dark-theme .main-header.navbar .navbar-nav .nav-item.show > .header-icon-button:not(#themeToggle) {
            background: #253146 !important;
            color: #fff !important;
            box-shadow: none !important;
            outline: 0 !important;
        }
        body.dark-theme .header-email-link .header-icon-button,
        body.dark-theme .header-email-link .header-icon-button:active { background: transparent !important; color: #cbd5e1 !important; }
        body.dark-theme .header-email-link .header-icon-button:hover,
        body.dark-theme .header-email-link .header-icon-button:focus { background: #253146 !important; color: #fff !important; }
        body.dark-theme #themeToggle { background: rgba(139, 211, 240, .08) !important; color: #8bd3f0 !important; }
        body.dark-theme #themeToggle:hover, body.dark-theme #themeToggle:focus { background: rgba(139, 211, 240, .16) !important; color: #c6efff !important; }
        body.dark-theme .menu-search-result, body.dark-theme .notification-content, body.dark-theme .notification-content strong { color: #dbe4f0; }
        body.dark-theme .menu-search-result:hover, body.dark-theme .notification-item.unread { background: #322332; color: #fff; }
        body.dark-theme .header-user-toggle { color: #dbe4f0 !important; }
        body.dark-theme .header-user-toggle:hover { background: #24344d; color: #fff !important; }
        body.dark-theme .header-user-dropdown { background: #182338 !important; border-color: #2f405b !important; box-shadow: 0 16px 34px rgba(0, 0, 0, .3); }
        body.dark-theme .header-user-dropdown .dropdown-item { color: #dbe4f0 !important; }
        body.dark-theme .header-user-dropdown .dropdown-item i { color: #8bd3f0 !important; }
        body.dark-theme .header-user-dropdown .dropdown-item:hover { background: #24344d !important; color: #fff !important; }
        body.dark-theme .maint-tag, body.dark-theme .config-note, body.dark-theme .maint-form-card { background: #1d2a40 !important; color: #cbd8e8; border-color: #40526d !important; }
        body.dark-theme .maint-status-muted { background: #33445e; color: #dbe4f0; }
        body.dark-theme .score-high { background: #183e31; color: #9ee7bd; }
        body.dark-theme .score-mid { background: #4a3a14; color: #f9d66b; }
        body.dark-theme .score-low { background: #4b2630; color: #ffb7c0; }
        body.dark-theme .weight-pill { background: #243d60; color: #b8dcff; }
        body.dark-theme .final-cell { background: #183e31 !important; }
        body.dark-theme .nav-tabs { border-bottom-color: #2f405b; }
        body.dark-theme .nav-tabs .nav-link { color: #aebed2; }
        body.dark-theme .nav-tabs .nav-link.active { background: #182338; color: #fff; border-color: #2f405b #2f405b #182338; }
        body.dark-theme .pagination .page-link { background: #182338; border-color: #2f405b; color: #c5d5e9; }
        body.dark-theme .pagination .page-item.active .page-link { background: #820005; border-color: #820005; color: #fff; }
        body.dark-theme .sidebar-dark-navy .user-panel { border-color: #2f405b !important; }
        body.dark-theme .sidebar-dark-navy .user-panel .info a, body.dark-theme .sidebar-dark-navy .user-panel small, body.dark-theme .sidebar-dark-navy .nav-sidebar > .nav-item > .nav-link, body.dark-theme .sidebar-dark-navy .nav-treeview > .nav-item > .nav-link { color: #cbd5e1 !important; }
        body.dark-theme .sidebar-dark-navy .nav-sidebar > .nav-item > .nav-link:hover, body.dark-theme .sidebar-dark-navy .nav-treeview > .nav-item > .nav-link:hover { background: #202c3f; color: #fff !important; }
        body.dark-theme .sidebar-dark-navy .nav-sidebar > .nav-item > .nav-link.active, body.dark-theme .sidebar-dark-navy .nav-treeview > .nav-item > .nav-link.active { background: #3d1c25; color: #fff !important; box-shadow: inset 0 0 0 1px #70303e; }
        body.dark-theme .sidebar-dark-navy .nav-sidebar .nav-icon { color: #aebcd0 !important; }
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
        /* Keep the dark sidebar rules after the light sidebar theme so they win by cascade. */
        body.dark-theme .sidebar-dark-navy .brand-link,
        body.dark-theme .sidebar-dark-navy .sidebar { background: #182338 !important; border-color: #2f405b !important; }
        body.dark-theme .sidebar-dark-navy .brand-link .brand-image-logo { background: #edf2f7; border-color: #40526d; }
        body.dark-theme .sidebar-dark-navy .nav-sidebar > .nav-item.menu-dashboard { border-color: #2f405b; }
        body.dark-theme .sidebar-dark-navy .nav-sidebar > .nav-item > .nav-link:hover,
        body.dark-theme .sidebar-dark-navy .nav-treeview > .nav-item > .nav-link:hover { background: #24344d !important; color: #fff !important; }
        body.dark-theme .sidebar-dark-navy .nav-sidebar > .nav-item > .nav-link.active,
        body.dark-theme .sidebar-dark-navy .nav-treeview > .nav-item > .nav-link.active {
            background: #3d1c25 !important;
            color: #fff !important;
            box-shadow: inset 0 0 0 1px #70303e !important;
        }
        body.dark-theme .sidebar-dark-navy .nav-sidebar > .nav-item.menu-dashboard > .nav-link { background: #211f32 !important; border-color: #70303e !important; }
        body.dark-theme .sidebar-dark-navy .nav-treeview { border-color: #40526d; }
        body.dark-theme .sidebar-dark-navy .nav-sidebar > .nav-item.menu-group > .nav-link .right { color: #9cacbf !important; }
        body.dark-theme .main-header .nav-link { color: #dbe4f0 !important; }
        body.dark-theme .main-header .nav-link:hover { color: #fff !important; }
        body.dark-theme .dropdown-menu .dropdown-item,
        body.dark-theme .dropdown-menu .dropdown-item button { color: #dbe4f0 !important; }
        body.dark-theme .dropdown-menu .dropdown-item i { color: #9cb4d0 !important; }
        body.dark-theme .dropdown-menu .dropdown-item:hover,
        body.dark-theme .dropdown-menu .dropdown-item:focus,
        body.dark-theme .dropdown-menu .dropdown-item:hover button,
        body.dark-theme .dropdown-menu .dropdown-item:focus button { background: #24344d !important; color: #fff !important; }
        body.dark-theme .dropdown-menu .dropdown-divider { border-color: #2f405b !important; }
        body.dark-theme .collector-grid,
        body.dark-theme .collector-table-card .bg-white { background: #182338 !important; }
        body.dark-theme .collector-table-card .table,
        body.dark-theme .collector-table-card tbody td,
        body.dark-theme .collector-table-card tbody .collector-sticky-number,
        body.dark-theme .collector-table-card tbody .collector-sticky-student {
            background: #182338 !important;
            color: #dbe4f0 !important;
            border-color: #2f405b !important;
        }
        body.dark-theme .collector-table-card thead th,
        body.dark-theme .collector-table-card thead .collector-sticky-number,
        body.dark-theme .collector-table-card thead .collector-sticky-student {
            background: #1d2a40 !important;
            color: #edf4fc !important;
            border-color: #40526d !important;
        }
        body.dark-theme .collector-table-card .final-cell { background: #173f34 !important; color: #d9ffe9 !important; }
        body.dark-theme .collector-readonly .grade-input[disabled] { background: #1d2a40 !important; color: #c5d5e9 !important; }
        body.dark-theme .callout { background: #182338 !important; border-left-color: #168fbd; color: #dbe4f0; }
        body.dark-theme .callout h1,
        body.dark-theme .callout h2,
        body.dark-theme .callout h3,
        body.dark-theme .callout h4,
        body.dark-theme .callout h5,
        body.dark-theme .callout h6 { color: #edf4fc; }
        body.dark-theme .callout p { color: #b9c9de; }
        body.dark-theme .products-list,
        body.dark-theme .products-list > .item { background: #182338 !important; border-color: #2f405b !important; }
        body.dark-theme .products-list > .item .product-title { color: #edf4fc !important; }
        body.dark-theme .products-list > .item .product-description { color: #9cadc2 !important; }
        @media (max-width: 991.98px) {
            .maint-search-grid { grid-template-columns: 1fr; }
            body { overflow-x: hidden; }
            .main-sidebar,
            .main-sidebar::before { transform: translateX(-250px); transition: transform .25s ease; }
            body.sidebar-open .main-sidebar,
            body.sidebar-open .main-sidebar::before { transform: translateX(0); }
            .content-wrapper,
            .main-footer,
            .main-header { margin-left: 0 !important; }
            .content-wrapper { min-width: 0; }
        }
        @media (max-width: 767.98px) {
            .topbar-search { width: auto; flex: 1; margin: 0 .45rem; }
            .topbar-search .form-control { min-width: 0; }
            .header-icon-button { width: 34px; height: 34px; }
            .header-email-link { display: none !important; }
            .content-header { padding: .8rem 0 .35rem; }
            .content-header h1 { font-size: 1.55rem; }
            .content-header .breadcrumb { display: none; }
            .content .container-fluid { padding-right: .65rem; padding-left: .65rem; }
            .card-header { padding: .8rem .9rem; }
            .card-body { padding: .9rem; }
            .maint-toolbar { align-items: stretch; }
            .maint-actions { width: 100%; }
            .maint-actions .btn { flex: 1 1 145px; }
            .filter-toolbar { display: grid; grid-template-columns: 1fr; gap: .65rem; }
            .filter-toolbar .form-group { min-width: 0; width: 100%; }
            .card-header .card-tools { float: none; margin-top: .7rem; }
            .modal-dialog { margin: .5rem; }
            .modal-dialog.modal-dialog-centered { min-height: calc(100% - 1rem); }
            .table-responsive { -webkit-overflow-scrolling: touch; }
            .maint-table thead th, .maint-table tbody td { padding: .7rem .65rem; }
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed {{ auth()->user()?->tema === 'dark' ? 'dark-theme' : '' }}">
@php
    $user = auth()->user();
    $canAccessEmails = $user !== null && $user->hasMenuAccess('emails');
    $initials = strtoupper(substr($user?->nombres ?? 'A', 0, 1).substr($user?->apellidos ?? 'D', 0, 1));
    $hasUserAvatar = filled($user?->avatar);
    $userAvatarUrl = $hasUserAvatar ? $user->avatar_url : null;
    $profileUrl = \App\Support\AppUrl::route('profile.show');
    $menuSearchItems = collect($menu)->flatMap(function (array $item) {
        $children = $item['children'] ?? [];

        return count($children) > 0
            ? collect($children)
            : collect([['label' => $item['label'], 'icon' => $item['icon'], 'url' => $item['url']]]);
    })->values();
@endphp
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li>
        </ul>
        <div class="topbar-search">
            <i class="fas fa-search"></i>
            <input id="menuSearchInput" type="search" class="form-control" autocomplete="off" placeholder="Buscar menús">
            <div id="menuSearchResults" class="menu-search-results"></div>
        </div>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <button type="button" class="nav-link header-icon-button" id="themeToggle" aria-label="Cambiar tema" title="Cambiar tema"><i class="far fa-moon"></i></button>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link header-icon-button" data-toggle="dropdown" href="#" aria-label="Notificaciones" title="Notificaciones">
                    <i class="far fa-bell"></i>
                    @if ($unreadNotificationCount > 0)<span class="header-icon-badge">{{ min($unreadNotificationCount, 99) }}</span>@endif
                </a>
                <div class="dropdown-menu dropdown-menu-right notification-dropdown">
                    <div class="notification-title">Notificaciones {{ $unreadNotificationCount ? '('.$unreadNotificationCount.')' : '' }}</div>
                    @forelse ($headerNotifications as $notification)
                        <div class="notification-item {{ $notification->leida_en ? '' : 'unread' }}">
                            <form method="POST" action="{{ \App\Support\AppUrl::route('notifications.read', ['notification' => $notification->id]) }}">@csrf<button class="notification-mark" title="Marcar como leída"><i class="fas {{ $notification->tipo === 'warning' ? 'fa-exclamation' : ($notification->tipo === 'success' ? 'fa-check' : 'fa-info') }}"></i></button></form>
                            @if ($notification->url)<a class="notification-content" href="{{ app_nav_url($notification->url) }}">@else<div class="notification-content">@endif
                                <strong>{{ $notification->titulo }}</strong><small>{{ $notification->mensaje }}</small><small>{{ $notification->created_at->diffForHumans() }}</small>
                            @if ($notification->url)</a>@else</div>@endif
                        </div>
                    @empty
                        <div class="menu-search-empty py-4"><i class="far fa-bell d-block mb-2"></i>No hay notificaciones nuevas.</div>
                    @endforelse
                </div>
            </li>
            @if ($canAccessEmails)
                <li class="nav-item header-email-link"><a href="{{ \App\Support\AppUrl::route('emails.index') }}" class="nav-link header-icon-button" title="Correos"><i class="far fa-envelope"></i></a></li>
            @endif
            <li class="nav-item dropdown">
                <a class="nav-link header-user-toggle" data-toggle="dropdown" href="#" aria-label="Menú de usuario">
                    @if ($hasUserAvatar)
                        <img src="{{ $userAvatarUrl }}" alt="Avatar" class="user-avatar-image mr-1">
                    @else
                        <span class="user-avatar-initials user-avatar-top">{{ $initials }}</span>
                    @endif
                    <span class="d-none d-md-inline header-user-name">{{ trim(($user->nombres ?? '').' '.($user->apellidos ?? '')) ?: 'Usuario' }}</span>
                    <i class="fas fa-chevron-down d-none d-md-inline" style="font-size:.65rem;"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right header-user-dropdown">
                    <a href="{{ $profileUrl }}" class="dropdown-item"><i class="fas fa-user-circle mr-2"></i>Mi perfil</a>
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
                <div class="image">
                    @if ($hasUserAvatar)
                        <img src="{{ $userAvatarUrl }}" alt="Avatar" class="user-avatar-sidebar">
                    @else
                        <span class="user-avatar-initials user-avatar-side">{{ $initials }}</span>
                    @endif
                </div>
                <div class="info">
                    <a href="{{ $profileUrl }}" class="d-block text-white">{{ trim(($user->nombres ?? '').' '.($user->apellidos ?? '')) ?: 'Usuario' }}</a>
                    <small class="text-light text-capitalize">{{ $user->role->nombre ?? 'Sin perfil' }}</small>
                </div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    @foreach ($menu as $key => $item)
                        @php
                            $children = $item['children'] ?? [];
                            $isChildActive = collect($children)->contains(
                                fn ($child) => $activeMenu === $child['key']
                            );
                            $itemClasses = ['nav-item'];

                            if ($key === 'dashboard') {
                                $itemClasses[] = 'menu-dashboard';
                            }

                            if (count($children) > 0) {
                                $itemClasses[] = 'has-treeview';
                                $itemClasses[] = 'menu-group';

                                if ($activeMenu === $key || $isChildActive) {
                                    $itemClasses[] = 'menu-open';
                                }
                            }
                        @endphp
                        <li class="{{ implode(' ', $itemClasses) }}">
                            <a href="{{ count($children) > 0 ? '#' : $item['url'] }}" class="nav-link {{ $activeMenu === $key || $isChildActive ? 'active' : '' }} {{ count($children) > 0 ? 'submenu-toggle' : '' }}" @if(count($children) > 0) data-submenu-toggle="true" role="button" aria-expanded="{{ $activeMenu === $key || $isChildActive ? 'true' : 'false' }}" @endif>
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
@php
    $savedTheme = in_array(data_get($user ?? null, 'tema'), ['light', 'dark'], true)
        ? data_get($user, 'tema')
        : 'light';
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const themeToggle = document.getElementById('themeToggle');
        const savedTheme = @json($savedTheme);
        const themeUpdateUrl = @json(\App\Support\AppUrl::route('profile.theme'));
        const csrfToken = @json(csrf_token());
        const applyTheme = function (theme) {
            const dark = theme === 'dark';
            document.body.classList.toggle('dark-theme', dark);
            themeToggle?.querySelector('i')?.classList.add('fa-moon');
            themeToggle?.querySelector('i')?.classList.remove('fa-sun');
            themeToggle?.setAttribute('title', dark ? 'Usar tema claro' : 'Usar tema oscuro');
        };

        applyTheme(savedTheme === 'dark' ? 'dark' : 'light');
        themeToggle?.addEventListener('click', function () {
            const nextTheme = document.body.classList.contains('dark-theme') ? 'light' : 'dark';
            window.localStorage.setItem('aci-theme', nextTheme);
            applyTheme(nextTheme);

            fetch(themeUpdateUrl, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ tema: nextTheme }),
            }).catch(function () {
                // Local storage keeps the last choice until the connection is restored.
            });
        });

        const menuSearchInput = document.getElementById('menuSearchInput');
        const menuSearchResults = document.getElementById('menuSearchResults');
        const allowedMenuItems = @json($menuSearchItems);
        const renderMenuResults = function () {
            if (!menuSearchInput || !menuSearchResults) return;

            const term = menuSearchInput.value.trim().toLocaleLowerCase();
            const matches = term === '' ? [] : allowedMenuItems.filter(function (item) {
                return item.label.toLocaleLowerCase().includes(term);
            }).slice(0, 7);
            menuSearchResults.replaceChildren();

            if (term !== '' && matches.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'menu-search-empty';
                empty.textContent = 'No tienes un menú autorizado con ese nombre.';
                menuSearchResults.appendChild(empty);
            }

            matches.forEach(function (item) {
                const link = document.createElement('a');
                link.className = 'menu-search-result';
                link.href = item.url;
                const icon = document.createElement('i');
                icon.className = item.icon || 'far fa-circle';
                const label = document.createElement('span');
                label.textContent = item.label;
                link.append(icon, label);
                menuSearchResults.appendChild(link);
            });

            menuSearchResults.classList.toggle('show', term !== '');
        };

        menuSearchInput?.addEventListener('input', renderMenuResults);
        menuSearchInput?.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter') return;
            const firstResult = menuSearchResults?.querySelector('.menu-search-result');
            if (firstResult) {
                event.preventDefault();
                window.location.assign(firstResult.href);
            }
        });
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.topbar-search')) menuSearchResults?.classList.remove('show');
        });

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

        // On mobile, keep the submenu tap independent from AdminLTE's desktop treeview handler.
        document.addEventListener('click', function (event) {
            const toggle = event.target.closest('.submenu-toggle[data-submenu-toggle="true"]');

            if (!toggle || window.innerWidth >= 992) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            const item = toggle.closest('.nav-item');
            const tree = item ? item.querySelector(':scope > .nav-treeview') : null;

            if (!item || !tree) {
                return;
            }

            const isOpen = !item.classList.contains('menu-open');
            item.classList.toggle('menu-open', isOpen);
            toggle.classList.toggle('active', isOpen);
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            tree.style.display = isOpen ? 'block' : 'none';
        }, true);
    });
</script>
<x-pwa-install />
</body>
</html>
