@extends('layouts.app')
@section('title', "Journal d'audit")
@section('subtitle', 'Historique des actions')

@section('content')
<div class="space-y-6">

    <div class="flex flex-wrap gap-2" id="actionFilters">
        <button onclick="filterAction('')" data-action="" class="btn btn-sm btn-primary">Tous</button>
        <button onclick="filterAction('login')" data-action="login" class="btn btn-sm btn-outline">Connexion</button>
        <button onclick="filterAction('create')" data-action="create" class="btn btn-sm btn-outline">Création</button>
        <button onclick="filterAction('update')" data-action="update" class="btn btn-sm btn-outline">Modification</button>
        <button onclick="filterAction('delete')" data-action="delete" class="btn btn-sm btn-outline">Suppression</button>
    </div>

    <div class="card" style="padding:0;overflow:hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="auditList">
                    <tr><td colspan="5" class="text-center text-muted-foreground py-8">Chargement...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="auditPagination" class="flex justify-center items-center gap-2"></div>

    <div id="emptyState" class="hidden">
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-4" style="background:var(--color-secondary)">
                <svg class="w-10 h-10" style="color:var(--color-muted-foreground)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </div>
            <p class="text-lg font-medium text-foreground">Aucun log d'audit</p>
            <p class="text-sm text-muted-foreground mt-1">Aucune action enregistrée pour ce filtre</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var currentAction = '';
var currentPage = 1;

var actionBadgeMap = {
    login:    { cls: 'badge badge-success', label: 'Connexion' },
    logout:   { cls: 'badge badge-success', label: 'Déconnexion' },
    create:   { cls: 'badge badge-info', label: 'Création' },
    update:   { cls: 'badge badge-warning', label: 'Modification' },
    delete:   { cls: 'badge badge-destructive', label: 'Suppression' }
};

function getActionBadge(action) {
    var m = actionBadgeMap[action];
    if (!m) m = { cls: 'badge badge-secondary', label: action };
    return '<span class="' + m.cls + '">' + m.label + '</span>';
}

function getUserAvatar(name) {
    var initial = (name || 'S').charAt(0).toUpperCase();
    var colors = [
        'oklch(0.55 0.2 260)', 'oklch(0.55 0.2 290)', 'oklch(0.55 0.2 145)',
        'oklch(0.55 0.2 25)', 'oklch(0.70 0.18 65)', 'oklch(0.55 0.2 240)'
    ];
    var colorIndex = (name || '').charCodeAt(0) % colors.length;
    var bgColor = colors[colorIndex];
    return '<div style="width:28px;height:28px;border-radius:9999px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;background:' + bgColor + ';flex-shrink:0">' + initial + '</div>';
}

function filterAction(val) {
    currentAction = val;
    currentPage = 1;
    document.querySelectorAll('#actionFilters .btn').forEach(function(btn) {
        if (btn.getAttribute('data-action') === val) {
            btn.className = 'btn btn-sm btn-primary';
        } else {
            btn.className = 'btn btn-sm btn-outline';
        }
    });
    loadAuditLogs();
}

async function loadAuditLogs(page) {
    if (page) currentPage = page;
    var url = '/api/v1/audit-logs?page=' + currentPage;
    if (currentAction) url += '&action=' + currentAction;

    var tbody = document.getElementById('auditList');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted-foreground py-8">Chargement...</td></tr>';
    document.getElementById('emptyState').classList.add('hidden');

    try {
        var data = await apiCall(url);

        if (!data.data || !data.data.length) {
            tbody.innerHTML = '';
            document.getElementById('emptyState').classList.remove('hidden');
            document.getElementById('auditPagination').innerHTML = '';
            return;
        }

        document.getElementById('emptyState').classList.add('hidden');

        tbody.innerHTML = data.data.map(function(l) {
            var userName = l.user ? l.user.name : 'Système';
            return '<tr>' +
                '<td>' +
                    '<div class="flex items-center gap-2">' +
                        getUserAvatar(userName) +
                        '<span class="text-foreground text-sm font-medium">' + userName + '</span>' +
                    '</div>' +
                '</td>' +
                '<td>' + getActionBadge(l.action) + '</td>' +
                '<td class="text-muted-foreground text-xs" style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + (l.description || '-') + '</td>' +
                '<td class="text-muted-foreground text-xs font-mono">' + (l.ip_address || '-') + '</td>' +
                '<td class="text-muted-foreground text-xs">' + (l.created_at || '') + '</td>' +
            '</tr>';
        }).join('');

        renderPagination(data);
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted-foreground py-8">Erreur de chargement</td></tr>';
        showToast('Erreur', "Impossible de charger les logs d'audit", 'error');
    }
}

function renderPagination(data) {
    var container = document.getElementById('auditPagination');
    if (!data || data.last_page <= 1) { container.innerHTML = ''; return; }
    var html = '';
    var prevDisabled = data.current_page <= 1;
    html += '<button onclick="loadAuditLogs(' + (data.current_page - 1) + ')" ' + (prevDisabled ? 'disabled' : '') + ' class="btn btn-sm btn-outline" ' + (prevDisabled ? 'style="opacity:0.5;cursor:not-allowed"' : '') + '>&laquo;</button>';
    var start = Math.max(1, data.current_page - 2);
    var end = Math.min(data.last_page, data.current_page + 2);
    for (var i = start; i <= end; i++) {
        if (i === data.current_page) {
            html += '<button class="btn btn-sm btn-primary">' + i + '</button>';
        } else {
            html += '<button onclick="loadAuditLogs(' + i + ')" class="btn btn-sm btn-outline">' + i + '</button>';
        }
    }
    var nextDisabled = data.current_page >= data.last_page;
    html += '<button onclick="loadAuditLogs(' + (data.current_page + 1) + ')" ' + (nextDisabled ? 'disabled' : '') + ' class="btn btn-sm btn-outline" ' + (nextDisabled ? 'style="opacity:0.5;cursor:not-allowed"' : '') + '>&raquo;</button>';
    container.innerHTML = html;
}

loadAuditLogs();
</script>
@endpush