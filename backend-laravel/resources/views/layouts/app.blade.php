<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MonitorIA') - MonitorIA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { margin: 0; padding: 0; background: #09090b; color: #fafafa; font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .layout-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #0a0a0f; border-right: 1px solid rgba(255,255,255,0.06); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; z-index: 50; }
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .header { height: 64px; background: #0a0a0f; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: space-between; padding: 0 24px; position: sticky; top: 0; z-index: 40; }
        .main-content { flex: 1; padding: 24px; }
        .sidebar-logo { display: flex; align-items: center; gap: 10px; padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.06); height: 64px; }
        .sidebar-logo svg { width: 28px; height: 28px; color: #6366f1; }
        .sidebar-logo span { font-size: 18px; font-weight: 700; letter-spacing: -0.025em; background: linear-gradient(135deg, #6366f1, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .sidebar-nav { flex: 1; padding: 12px 12px; overflow-y: auto; }
        .sidebar-section-title { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #52525b; padding: 8px 12px; margin-top: 8px; }
        .sidebar-link { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 8px; color: #a1a1aa; text-decoration: none; font-size: 13.5px; font-weight: 500; transition: all 0.15s ease; margin-bottom: 2px; }
        .sidebar-link:hover { background: rgba(255,255,255,0.05); color: #e4e4e7; }
        .sidebar-link.active { background: rgba(99,102,241,0.15); color: #6366f1; }
        .sidebar-link svg { width: 18px; height: 18px; flex-shrink: 0; }
        .sidebar-footer { border-top: 1px solid rgba(255,255,255,0.06); padding: 14px 16px; }
        .sidebar-user { display: flex; align-items: center; gap: 10px; }
        .sidebar-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; color: #fff; flex-shrink: 0; }
        .sidebar-user-info { flex: 1; min-width: 0; }
        .sidebar-user-name { font-size: 13px; font-weight: 600; color: #e4e4e7; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user-role { font-size: 11px; color: #71717a; }
        .sidebar-logout { background: none; border: none; cursor: pointer; padding: 6px; border-radius: 6px; color: #71717a; transition: all 0.15s ease; display: flex; align-items: center; }
        .sidebar-logout:hover { background: rgba(255,255,255,0.05); color: #ef4444; }
        .sidebar-logout svg { width: 18px; height: 18px; }
        .header-left { display: flex; flex-direction: column; }
        .header-title { font-size: 17px; font-weight: 600; color: #fafafa; line-height: 1.2; }
        .header-subtitle { font-size: 12px; color: #71717a; margin-top: 1px; }
        .header-right { display: flex; align-items: center; gap: 16px; }
        .header-clock { font-size: 13px; color: #a1a1aa; font-variant-numeric: tabular-nums; font-weight: 500; }
        .header-bell { position: relative; background: none; border: none; cursor: pointer; color: #71717a; padding: 6px; border-radius: 6px; transition: all 0.15s ease; text-decoration: none; display: flex; align-items: center; }
        .header-bell:hover { color: #e4e4e7; background: rgba(255,255,255,0.05); }
        .header-bell svg { width: 20px; height: 20px; }
        .alert-badge { position: absolute; top: 2px; right: 2px; min-width: 18px; height: 18px; border-radius: 9px; background: #ef4444; color: #fff; font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center; padding: 0 4px; line-height: 1; animation: badge-pulse 2s ease-in-out infinite; }
        @keyframes badge-pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }
        .toast-container { position: fixed; top: 80px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; pointer-events: none; }
        .toast { pointer-events: auto; min-width: 340px; max-width: 420px; background: #18181b; border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 14px 16px; display: flex; gap: 12px; align-items: flex-start; animation: toast-slide-in 0.3s ease forwards; box-shadow: 0 8px 30px rgba(0,0,0,0.5); position: relative; overflow: hidden; }
        .toast.toast-removing { animation: toast-slide-out 0.25s ease forwards; }
        .toast-icon { width: 20px; height: 20px; flex-shrink: 0; margin-top: 1px; }
        .toast-success .toast-icon { color: #22c55e; }
        .toast-error .toast-icon { color: #ef4444; }
        .toast-warning .toast-icon { color: #f59e0b; }
        .toast-info .toast-icon { color: #3b82f6; }
        .toast-content { flex: 1; min-width: 0; }
        .toast-title { font-size: 13.5px; font-weight: 600; color: #fafafa; }
        .toast-message { font-size: 12.5px; color: #a1a1aa; margin-top: 2px; line-height: 1.4; }
        .toast-close { background: none; border: none; cursor: pointer; color: #52525b; padding: 2px; border-radius: 4px; transition: all 0.15s ease; flex-shrink: 0; }
        .toast-close:hover { color: #e4e4e7; background: rgba(255,255,255,0.05); }
        .toast-close svg { width: 16px; height: 16px; }
        .toast-progress { position: absolute; bottom: 0; left: 0; height: 2px; border-radius: 0 0 10px 10px; animation: toast-progress 4s linear forwards; }
        .toast-success .toast-progress { background: #22c55e; }
        .toast-error .toast-progress { background: #ef4444; }
        .toast-warning .toast-progress { background: #f59e0b; }
        .toast-info .toast-progress { background: #3b82f6; }
        @keyframes toast-slide-in { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes toast-slide-out { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
        @keyframes toast-progress { from { width: 100%; } to { width: 0%; } }
    </style>
</head>
<body>
    <div id="toast-container" class="toast-container"></div>

    <div class="layout-wrapper" id="app-layout" style="display:none;">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="12" width="4" height="9" rx="1"/><rect x="10" y="8" width="4" height="13" rx="1"/><rect x="17" y="4" width="4" height="17" rx="1"/></svg>
                <span>MonitorIA</span>
            </div>

            <nav class="sidebar-nav">
                <a href="/dashboard" class="sidebar-link" data-path="/dashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    Dashboard
                </a>
                <a href="/servers" class="sidebar-link" data-path="/servers">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><circle cx="6" cy="6" r="1" fill="currentColor"/><circle cx="6" cy="18" r="1" fill="currentColor"/></svg>
                    Serveurs
                </a>
                <a href="/alerts" class="sidebar-link" data-path="/alerts">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    Alertes
                    <span id="sidebar-alert-badge" style="display:none;margin-left:auto;min-width:20px;height:20px;border-radius:10px;background:#ef4444;color:#fff;font-size:10px;font-weight:700;display:none;align-items:center;justify-content:center;padding:0 5px;line-height:1"></span>
                </a>
                <a href="/anomalies" class="sidebar-link" data-path="/anomalies">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 22h20L12 2z"/><line x1="12" y1="10" x2="12" y2="16"/><circle cx="12" cy="18.5" r="0.5" fill="currentColor"/></svg>
                    Anomalies IA
                </a>
                <a href="/incidents" class="sidebar-link" data-path="/incidents">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Incidents
                </a>
                <a href="/logs" class="sidebar-link" data-path="/logs">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    Logs
                </a>
                <a href="/settings" class="sidebar-link" data-path="/settings">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    Parametres
                </a>

                <div class="sidebar-section-title" id="admin-section-title" style="display:none;">Administration</div>
                <div id="admin-section" style="display:none;">
                    <a href="/admin/users" class="sidebar-link" data-path="/admin/users">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Utilisateurs
                    </a>
                    <a href="/admin/audit-logs" class="sidebar-link" data-path="/admin/audit-logs">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        Journal d'audit
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-avatar" id="user-avatar">?</div>
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name" id="user-name">-</div>
                        <div class="sidebar-user-role" id="user-role">-</div>
                    </div>
                    <button class="sidebar-logout" id="logout-btn" title="Deconnexion">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        <div class="main-wrapper">
            <header class="header">
                <div class="header-left">
                    <div class="header-title">@yield('title', 'Dashboard')</div>
                    <div class="header-subtitle">@yield('subtitle', '')</div>
                </div>
                <div class="header-right">
                    <div class="header-clock" id="header-clock">--:--:--</div>
                    <a href="/alerts" class="header-bell" title="Alertes">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        <span class="alert-badge" id="alert-badge" style="display:none">0</span>
                    </a>
                </div>
            </header>

            <main class="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
    (function() {
        function showToast(title, message, type) {
            type = type || 'info';
            var container = document.getElementById('toast-container');
            var toast = document.createElement('div');
            toast.className = 'toast toast-' + type;

            var icons = {
                success: '<svg class="toast-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
                error: '<svg class="toast-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
                warning: '<svg class="toast-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
                info: '<svg class="toast-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
            };

            toast.innerHTML =
                (icons[type] || icons.info) +
                '<div class="toast-content">' +
                    '<div class="toast-title">' + (title || '') + '</div>' +
                    '<div class="toast-message">' + (message || '') + '</div>' +
                '</div>' +
                '<button class="toast-close"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>' +
                '<div class="toast-progress"></div>';

            var closeBtn = toast.querySelector('.toast-close');
            closeBtn.addEventListener('click', function() {
                removeToast(toast);
            });

            container.appendChild(toast);

            var timeout = setTimeout(function() {
                removeToast(toast);
            }, 4000);

            toast._timeout = timeout;

            function removeToast(el) {
                if (el._removed) return;
                el._removed = true;
                clearTimeout(el._timeout);
                el.classList.add('toast-removing');
                el.addEventListener('animationend', function() {
                    if (el.parentNode) el.parentNode.removeChild(el);
                });
            }
        }

        window.showToast = showToast;

        function apiCall(endpoint, options) {
            options = options || {};
            var token = localStorage.getItem('token');
            var headers = Object.assign({}, options.headers || {}, {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            });
            if (token) {
                headers['Authorization'] = 'Bearer ' + token;
            }
            var fetchOptions = Object.assign({}, options, {
                headers: headers
            });

            return fetch(endpoint, fetchOptions).then(function(response) {
                if (response.status === 401) {
                    localStorage.clear();
                    window.location.href = '/login';
                    return Promise.reject(new Error('Unauthorized'));
                }
                return response.json();
            });
        }

        window.apiCall = apiCall;

        function updateClock() {
            var now = new Date();
            var h = String(now.getHours()).padStart(2, '0');
            var m = String(now.getMinutes()).padStart(2, '0');
            var s = String(now.getSeconds()).padStart(2, '0');
            var el = document.getElementById('header-clock');
            if (el) el.textContent = h + ':' + m + ':' + s;
        }
        setInterval(updateClock, 1000);
        updateClock();

        function setActiveLink() {
            var path = window.location.pathname;
            var links = document.querySelectorAll('.sidebar-link');
            links.forEach(function(link) {
                var dataPath = link.getAttribute('data-path');
                if (dataPath && path.startsWith(dataPath)) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        }

        function init() {
            var token = localStorage.getItem('token');
            if (!token) {
                window.location.href = '/login';
                return;
            }

            var user = null;
            try {
                user = JSON.parse(localStorage.getItem('user'));
            } catch(e) {}

            if (!user) {
                window.location.href = '/login';
                return;
            }

            var layout = document.getElementById('app-layout');
            layout.style.display = 'flex';

            var avatar = document.getElementById('user-avatar');
            var nameEl = document.getElementById('user-name');
            var roleEl = document.getElementById('user-role');

            if (user.name) {
                avatar.textContent = user.name.charAt(0).toUpperCase();
                nameEl.textContent = user.name;
            }
            if (user.role) {
                roleEl.textContent = user.role;
            }

            if (user.role === 'administrateur') {
                var adminTitle = document.getElementById('admin-section-title');
                var adminSection = document.getElementById('admin-section');
                if (adminTitle) adminTitle.style.display = 'block';
                if (adminSection) adminSection.style.display = 'block';
            }

            setActiveLink();

            var logoutBtn = document.getElementById('logout-btn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function() {
                    apiCall('/api/v1/auth/logout', { method: 'POST' }).then(function() {
                        localStorage.clear();
                        window.location.href = '/login';
                    }).catch(function() {
                        localStorage.clear();
                        window.location.href = '/login';
                    });
                });
            }
        }

        function updateAlertBadge() {
            apiCall('/api/v1/alerts?status=active&per_page=1').then(function(data) {
                var total = data.total || 0;
                var badge = document.getElementById('alert-badge');
                var sidebarBadge = document.getElementById('sidebar-alert-badge');
                if (badge) {
                    if (total > 0) {
                        badge.textContent = total > 99 ? '99+' : total;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                }
                if (sidebarBadge) {
                    if (total > 0) {
                        sidebarBadge.textContent = total > 99 ? '99+' : total;
                        sidebarBadge.style.display = 'flex';
                    } else {
                        sidebarBadge.style.display = 'none';
                    }
                }
            }).catch(function() {});
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }

        setInterval(updateAlertBadge, 15000);
        setTimeout(updateAlertBadge, 2000);
    })();
    </script>

    @stack('scripts')
</body>
</html>