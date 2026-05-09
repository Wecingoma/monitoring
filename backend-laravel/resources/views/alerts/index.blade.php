@extends('layouts.app')
@section('title', 'Alertes')
@section('subtitle', 'Centre de notification')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <div class="flex flex-col gap-3 w-full lg:w-auto">
            <div class="flex items-center gap-2 flex-wrap" id="severityFilters">
                <span class="text-xs font-medium mr-1" style="color:var(--color-muted-foreground)">Sévérité :</span>
                <button onclick="setFilter('severity','')" class="btn btn-sm btn-primary" data-group="severity" data-value="">Toutes</button>
                <button onclick="setFilter('severity','critical')" class="btn btn-sm btn-outline" data-group="severity" data-value="critical">Critique</button>
                <button onclick="setFilter('severity','warning')" class="btn btn-sm btn-outline" data-group="severity" data-value="warning">Warning</button>
                <button onclick="setFilter('severity','info')" class="btn btn-sm btn-outline" data-group="severity" data-value="info">Info</button>
            </div>
            <div class="flex items-center gap-2 flex-wrap" id="statusFilters">
                <span class="text-xs font-medium mr-1" style="color:var(--color-muted-foreground)">Statut :</span>
                <button onclick="setFilter('status','')" class="btn btn-sm btn-primary" data-group="status" data-value="">Toutes</button>
                <button onclick="setFilter('status','active')" class="btn btn-sm btn-outline" data-group="status" data-value="active">Active</button>
                <button onclick="setFilter('status','acknowledged')" class="btn btn-sm btn-outline" data-group="status" data-value="acknowledged">Reconnue</button>
                <button onclick="setFilter('status','resolved')" class="btn btn-sm btn-outline" data-group="status" data-value="resolved">Résolue</button>
            </div>
            <div class="flex items-center gap-2 flex-wrap" id="sourceFilters">
                <span class="text-xs font-medium mr-1" style="color:var(--color-muted-foreground)">Source :</span>
                <button onclick="setFilter('source','')" class="btn btn-sm btn-primary" data-group="source" data-value="">Tous</button>
                <button onclick="setFilter('source','zabbix')" class="btn btn-sm btn-outline" data-group="source" data-value="zabbix">Zabbix</button>
                <button onclick="setFilter('source','elastic')" class="btn btn-sm btn-outline" data-group="source" data-value="elastic">Elastic</button>
                <button onclick="setFilter('source','ai')" class="btn btn-sm btn-outline" data-group="source" data-value="ai">IA</button>
                <button onclick="setFilter('source','manual')" class="btn btn-sm btn-outline" data-group="source" data-value="manual">Manuel</button>
            </div>
        </div>
        <button onclick="openCreateModal()" class="btn btn-primary">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Créer une alerte
        </button>
    </div>

    <div id="alertsGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-4"></div>

    <div id="emptyState" class="hidden">
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-4" style="background:var(--color-secondary)">
                <svg class="w-10 h-10" style="color:var(--color-muted-foreground)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </div>
            <p class="text-lg font-medium" style="color:var(--color-foreground)">Aucune alerte trouvée</p>
            <p class="text-sm mt-1" style="color:var(--color-muted-foreground)">Filtrez différement ou créez une nouvelle alerte</p>
        </div>
    </div>

    <div id="loadingState" class="hidden">
        <div class="flex items-center justify-center py-16">
            <svg class="spinner w-8 h-8" style="color:var(--color-primary)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        </div>
    </div>

    <div id="pagination" class="flex justify-center items-center gap-2"></div>
</div>

<div id="createModal" class="fixed inset-0 z-50 hidden" onclick="closeModalOnBackdrop(event)">
    <div class="absolute inset-0" style="background:oklch(0 0 0 / 0.6);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px)"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div id="createModalContent" class="card w-full max-w-lg transform translate-y-8 opacity-0" style="transition:all 0.3s ease">
            <div class="flex items-center justify-between mb-6">
                <h3 class="card-title" style="font-size:1.125rem">Créer une alerte</h3>
                <button onclick="closeCreateModal()" class="btn btn-ghost btn-icon" style="color:var(--color-muted-foreground)">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form id="createAlertForm" onsubmit="submitAlert(event)" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color:var(--color-foreground)">Titre <span style="color:var(--color-destructive)">*</span></label>
                    <input type="text" id="form_title" required class="input" placeholder="Titre de l'alerte">
                    <p id="err_title" class="text-xs mt-1 hidden" style="color:var(--color-destructive)"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color:var(--color-foreground)">Description</label>
                    <textarea id="form_description" rows="3" class="input" style="height:auto;padding-top:0.5rem;padding-bottom:0.5rem;resize:none" placeholder="Description détaillée..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:var(--color-foreground)">Sévérité <span style="color:var(--color-destructive)">*</span></label>
                        <select id="form_severity" required class="select">
                            <option value="critical">Critique</option>
                            <option value="warning">Warning</option>
                            <option value="info">Info</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:var(--color-foreground)">Source</label>
                        <select id="form_source" class="select">
                            <option value="manual">Manuel</option>
                            <option value="zabbix">Zabbix</option>
                            <option value="elastic">Elastic</option>
                            <option value="ai">IA</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color:var(--color-foreground)">Serveur</label>
                    <select id="form_server_id" class="select">
                        <option value="">Aucun serveur</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeCreateModal()" class="btn btn-outline">Annuler</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary">
                        <svg id="submitSpinner" class="spinner w-4 h-4 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Créer l'alerte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    @keyframes spin { to { transform: rotate(360deg); } }
    .spinner { animation: spin 0.7s linear infinite; }
    #createModalContent.show { transform: translateY(0); opacity: 1; }
</style>
<script>
var filters = { severity: '', status: '', source: '' };
var currentPage = 1;

function setFilter(group, value) {
    filters[group] = value;
    currentPage = 1;
    document.querySelectorAll('.btn-sm[data-group="' + group + '"]').forEach(function(btn) {
        if (btn.getAttribute('data-value') === value) {
            btn.className = 'btn btn-sm btn-primary';
        } else {
            btn.className = 'btn btn-sm btn-outline';
        }
    });
    loadAlerts();
}

function severityBadge(s) {
    var map = {
        critical: { cls: 'badge badge-destructive', label: 'Critique' },
        warning:  { cls: 'badge badge-warning', label: 'Warning' },
        info:     { cls: 'badge badge-info', label: 'Info' }
    };
    var c = map[s] || map.info;
    return '<span class="' + c.cls + '">' + c.label + '</span>';
}

function statusBadge(s) {
    var map = {
        active:      { cls: 'badge badge-destructive', label: 'Active' },
        acknowledged:{ cls: 'badge badge-warning', label: 'Reconnue' },
        resolved:    { cls: 'badge badge-success', label: 'Résolue' }
    };
    var c = map[s] || { cls: 'badge badge-secondary', label: s };
    return '<span class="' + c.cls + '">' + c.label + '</span>';
}

function sourceBadge(src) {
    var labels = { zabbix: 'Zabbix', elastic: 'Elastic', ai: 'IA', manual: 'Manuel' };
    return '<span class="badge badge-outline">' + (labels[src] || src) + '</span>';
}

function severityBorderClass(s) {
    var map = {
        critical: 'border-l-red-500',
        warning:  'border-l-yellow-500',
        info:     'border-l-blue-500'
    };
    return map[s] || 'border-l-blue-500';
}

function timeAgo(dateStr) {
    if (!dateStr) return 'N/A';
    var now = new Date();
    var date = new Date(dateStr);
    var seconds = Math.floor((now - date) / 1000);
    if (seconds < 0) return 'N/A';
    if (seconds < 60) return "À l'instant";
    var minutes = Math.floor(seconds / 60);
    if (minutes < 60) return minutes + ' min';
    var hours = Math.floor(minutes / 60);
    if (hours < 24) return hours + ' h';
    var days = Math.floor(hours / 24);
    if (days < 30) return days + ' j';
    return date.toLocaleDateString('fr-FR');
}

function truncate(str, len) {
    if (!str) return '';
    return str.length > len ? str.substring(0, len) + '...' : str;
}

function renderAlertCard(a) {
    var borderColor = { critical: 'oklch(0.55 0.2 25)', warning: 'oklch(0.70 0.18 65)', info: 'oklch(0.55 0.18 240)' };
    var blc = borderColor[a.severity] || borderColor.info;
    var isActive = a.status === 'active';
    var isResolved = a.status === 'resolved';

    var actions = '<div class="flex items-center gap-2 mt-3">';
    if (isActive) {
        actions += '<button onclick="acknowledgeAlert(' + a.id + ')" class="btn btn-sm btn-outline">Reconnaître</button>';
    }
    if (!isResolved) {
        actions += '<button onclick="resolveAlert(' + a.id + ')" class="btn btn-sm btn-primary">Résoudre</button>';
    }
    actions += '</div>';

    return '<div class="card" style="border-left:4px solid ' + blc + '">' +
        '<div class="flex items-center gap-2 mb-2 flex-wrap">' +
            severityBadge(a.severity) +
            statusBadge(a.status) +
            (a.source ? sourceBadge(a.source) : '') +
        '</div>' +
        '<h3 class="card-title mb-1">' + (a.title || 'Sans titre') + '</h3>' +
        (a.description ? '<p class="text-sm mb-2" style="color:var(--color-muted-foreground);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">' + truncate(a.description, 120) + '</p>' : '') +
        '<div class="flex items-center gap-2 text-xs" style="color:var(--color-muted-foreground)">' +
            (a.server ? '<span>' + a.server.name + '</span>' : '') +
            (a.server && a.created_at ? '<span>&middot;</span>' : '') +
            (a.created_at ? '<span>' + timeAgo(a.created_at) + '</span>' : '') +
        '</div>' +
        actions +
    '</div>';
}

function renderPagination(data) {
    var container = document.getElementById('pagination');
    if (!data || data.last_page <= 1) { container.innerHTML = ''; return; }
    var html = '';
    var prevDisabled = data.current_page <= 1;
    html += '<button onclick="loadAlerts(' + (data.current_page - 1) + ')" ' + (prevDisabled ? 'disabled' : '') + ' class="btn btn-sm btn-outline" ' + (prevDisabled ? 'style="opacity:0.5;cursor:not-allowed"' : '') + '>&laquo;</button>';
    var start = Math.max(1, data.current_page - 2);
    var end = Math.min(data.last_page, data.current_page + 2);
    for (var i = start; i <= end; i++) {
        if (i === data.current_page) {
            html += '<button class="btn btn-sm btn-primary">' + i + '</button>';
        } else {
            html += '<button onclick="loadAlerts(' + i + ')" class="btn btn-sm btn-outline">' + i + '</button>';
        }
    }
    var nextDisabled = data.current_page >= data.last_page;
    html += '<button onclick="loadAlerts(' + (data.current_page + 1) + ')" ' + (nextDisabled ? 'disabled' : '') + ' class="btn btn-sm btn-outline" ' + (nextDisabled ? 'style="opacity:0.5;cursor:not-allowed"' : '') + '>&raquo;</button>';
    container.innerHTML = html;
}

async function loadAlerts(page) {
    if (page) currentPage = page;
    var url = '/api/v1/alerts?page=' + currentPage;
    if (filters.severity) url += '&severity=' + filters.severity;
    if (filters.status) url += '&status=' + filters.status;
    if (filters.source) url += '&source=' + filters.source;

    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('alertsGrid').innerHTML = '';
    document.getElementById('emptyState').classList.add('hidden');

    try {
        var data = await apiCall(url);
        document.getElementById('loadingState').classList.add('hidden');
        if (!data.data || !data.data.length) {
            document.getElementById('emptyState').classList.remove('hidden');
            document.getElementById('pagination').innerHTML = '';
            return;
        }
        document.getElementById('alertsGrid').innerHTML = data.data.map(renderAlertCard).join('');
        renderPagination(data);
    } catch (e) {
        document.getElementById('loadingState').classList.add('hidden');
        showToast('Erreur', 'Impossible de charger les alertes', 'error');
    }
}

async function acknowledgeAlert(id) {
    try {
        await apiCall('/api/v1/alerts/' + id + '/acknowledge', { method: 'POST' });
        showToast('Alerte', 'Alerte reconnue avec succès', 'success');
        loadAlerts();
    } catch (e) {
        showToast('Erreur', 'Erreur lors de la reconnaissance', 'error');
    }
}

async function resolveAlert(id) {
    try {
        await apiCall('/api/v1/alerts/' + id + '/resolve', { method: 'POST' });
        showToast('Alerte', 'Alerte résolue avec succès', 'success');
        loadAlerts();
    } catch (e) {
        showToast('Erreur', 'Erreur lors de la résolution', 'error');
    }
}

function openCreateModal() {
    var modal = document.getElementById('createModal');
    var content = document.getElementById('createModalContent');
    modal.classList.remove('hidden');
    setTimeout(function() { content.classList.add('show'); }, 10);
    loadServersForSelect();
}

function closeCreateModal() {
    var content = document.getElementById('createModalContent');
    content.classList.remove('show');
    setTimeout(function() {
        document.getElementById('createModal').classList.add('hidden');
        document.getElementById('createAlertForm').reset();
        document.querySelectorAll('[id^="err_"]').forEach(function(el) { el.classList.add('hidden'); el.textContent = ''; });
    }, 300);
}

function closeModalOnBackdrop(event) {
    if (event.target === document.getElementById('createModal')) {
        closeCreateModal();
    }
}

async function loadServersForSelect() {
    try {
        var result = await apiCall('/api/v1/servers?per_page=200');
        var select = document.getElementById('form_server_id');
        select.innerHTML = '<option value="">Aucun serveur</option>';
        if (result.data) {
            result.data.forEach(function(s) {
                select.innerHTML += '<option value="' + s.id + '">' + s.name + ' (' + (s.ip_address || '') + ')</option>';
            });
        }
    } catch (e) {}
}

async function submitAlert(e) {
    e.preventDefault();
    var payload = {
        title: document.getElementById('form_title').value.trim(),
        description: document.getElementById('form_description').value.trim(),
        severity: document.getElementById('form_severity').value,
        source: document.getElementById('form_source').value,
        server_id: document.getElementById('form_server_id').value || null
    };

    var spinner = document.getElementById('submitSpinner');
    var btn = document.getElementById('submitBtn');
    spinner.classList.remove('hidden');
    btn.disabled = true;

    try {
        var result = await apiCall('/api/v1/alerts', {
            method: 'POST',
            body: JSON.stringify(payload)
        });
        if (result.errors) {
            Object.keys(result.errors).forEach(function(field) {
                var errEl = document.getElementById('err_' + field);
                if (errEl) {
                    errEl.textContent = result.errors[field][0];
                    errEl.classList.remove('hidden');
                }
            });
            return;
        }
        showToast('Alerte créée', "L'alerte a été créée avec succès", 'success');
        closeCreateModal();
        loadAlerts();
    } catch (e) {
        if (e.message !== 'Unauthorized') {
            showToast('Erreur', "Erreur lors de la création de l'alerte", 'error');
        }
    } finally {
        spinner.classList.add('hidden');
        btn.disabled = false;
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeCreateModal();
});

loadAlerts();
</script>
@endpush