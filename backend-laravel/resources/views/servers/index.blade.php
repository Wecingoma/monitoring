@extends('layouts.app')
@section('title', 'Serveurs')
@section('subtitle', 'Gestion et supervision')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex flex-col sm:flexrow items-start sm:items-center gap-3 w-full sm:w-auto">
            <input type="text" id="searchInput" placeholder="Rechercher un serveur..." class="input w-72">
            <div class="flex items-center gap-2 flex-wrap" id="statusFilters">
                <button onclick="setStatus('')" class="btn btn-sm btn-primary" data-status="">Tous</button>
                <button onclick="setStatus('online')" class="btn btn-sm btn-outline" data-status="online">En ligne</button>
                <button onclick="setStatus('offline')" class="btn btn-sm btn-outline" data-status="offline">Hors ligne</button>
                <button onclick="setStatus('warning')" class="btn btn-sm btn-outline" data-status="warning">Attention</button>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button id="syncZabbixBtn" onclick="syncZabbix()" class="btn btn-outline">
                <svg id="syncIcon" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0118.8-4.3M22 12.5a10 10 0 01-18.8 4.3"/></svg>
                <svg id="syncSpinner" class="spinner w-4 h-4 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Synchroniser Zabbix
            </button>
            <button onclick="openAddModal()" class="btn btn-primary">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Ajouter un serveur
            </button>
        </div>
    </div>

    <div id="serversGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"></div>

    <div id="emptyState" class="hidden">
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-4" style="background:var(--color-secondary)">
                <svg class="w-10 h-10" style="color:var(--color-muted-foreground)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><circle cx="6" cy="6" r="1" fill="currentColor"/><circle cx="6" cy="18" r="1" fill="currentColor"/></svg>
            </div>
            <p class="text-lg font-medium" style="color:var(--color-foreground)">Aucun serveur trouvé</p>
            <p class="text-sm mt-1" style="color:var(--color-muted-foreground)">Ajoutez un serveur ou synchronisez depuis Zabbix</p>
        </div>
    </div>

    <div id="loadingState" class="hidden">
        <div class="flex items-center justify-center py-16">
            <svg class="spinner w-8 h-8" style="color:var(--color-primary)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        </div>
    </div>

    <div id="pagination" class="flex justify-center items-center gap-2"></div>
</div>

<div id="addModal" class="fixed inset-0 z-50 hidden" onclick="closeModalOnBackdrop(event)">
    <div class="absolute inset-0" style="background:oklch(0 0 0 / 0.6);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px)"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div id="addModalContent" class="card w-full max-w-lg transform translate-y-8 opacity-0" style="transition:all 0.3s ease">
            <div class="flex items-center justify-between mb-6">
                <h3 class="card-title" style="font-size:1.125rem">Ajouter un serveur</h3>
                <button onclick="closeAddModal()" class="btn btn-ghost btn-icon" style="color:var(--color-muted-foreground)">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form id="addServerForm" onsubmit="submitServer(event)" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color:var(--color-foreground)">Nom <span style="color:var(--color-destructive)">*</span></label>
                    <input type="text" id="form_name" required class="input" placeholder="Production Web 01">
                    <p id="err_name" class="text-xs mt-1 hidden" style="color:var(--color-destructive)"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color:var(--color-foreground)">Hostname <span style="color:var(--color-destructive)">*</span></label>
                    <input type="text" id="form_hostname" required class="input" placeholder="web01.example.local">
                    <p id="err_hostname" class="text-xs mt-1 hidden" style="color:var(--color-destructive)"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color:var(--color-foreground)">Adresse IP <span style="color:var(--color-destructive)">*</span></label>
                    <input type="text" id="form_ip_address" required class="input" placeholder="192.168.1.10">
                    <p id="err_ip_address" class="text-xs mt-1 hidden" style="color:var(--color-destructive)"></p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:var(--color-foreground)">Type OS</label>
                        <select id="form_os_type" class="select">
                            <option value="linux">Linux</option>
                            <option value="windows">Windows</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:var(--color-foreground)">Emplacement</label>
                        <input type="text" id="form_location" class="input" placeholder="DC Paris">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color:var(--color-foreground)">Description</label>
                    <textarea id="form_description" rows="3" class="input" style="height:auto;padding-top:0.5rem;padding-bottom:0.5rem;resize:none" placeholder="Description du serveur..."></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeAddModal()" class="btn btn-outline">Annuler</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary">
                        <svg id="submitSpinner" class="spinner w-4 h-4 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Créer le serveur
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
    #addModalContent.show { transform: translateY(0); opacity: 1; }
</style>
<script>
var currentStatus = '';
var currentPage = 1;
var searchTimeout;

function setStatus(status) {
    currentStatus = status;
    currentPage = 1;
    document.querySelectorAll('#statusFilters .btn').forEach(function(btn) {
        if (btn.getAttribute('data-status') === status) {
            btn.className = 'btn btn-sm btn-primary';
        } else {
            btn.className = 'btn btn-sm btn-outline';
        }
    });
    loadServers();
}

function getStatusBadge(status) {
    var map = {
        online:  { cls: 'badge badge-success', label: 'En ligne' },
        offline: { cls: 'badge badge-destructive', label: 'Hors ligne' },
        warning: { cls: 'badge badge-warning', label: 'Attention' },
        unknown: { cls: 'badge badge-secondary', label: 'Inconnu' }
    };
    var s = map[status] || map.unknown;
    return '<span class="' + s.cls + '">' + s.label + '</span>';
}

function getProgressBar(label, value, colorClass) {
    var val = (value !== null && value !== undefined) ? value : 0;
    return '<div class="flex items-center gap-2 mt-1">' +
        '<span class="text-xs w-10" style="color:var(--color-muted-foreground)">' + label + '</span>' +
        '<div class="progress ' + colorClass + '" style="flex:1;min-width:60px">' +
            '<div class="progress-bar" style="width:' + Math.min(val, 100) + '%"></div>' +
        '</div>' +
        '<span class="text-xs font-mono w-8" style="color:var(--color-muted-foreground)">' + val + '%</span>' +
    '</div>';
}

function renderServerCard(s) {
    var statusBadge = getStatusBadge(s.status);
    var cpuColor = s.cpu_usage > 90 ? 'progress-red' : s.cpu_usage > 75 ? 'progress-amber' : 'progress-blue';
    var ramColor = s.ram_usage > 90 ? 'progress-red' : s.ram_usage > 75 ? 'progress-amber' : 'progress-green';
    var diskColor = s.disk_usage > 90 ? 'progress-red' : s.disk_usage > 75 ? 'progress-amber' : 'progress-amber';

    return '<div class="card">' +
        '<div class="flex items-start justify-between mb-3">' +
            '<div class="min-w-0 flex-1 mr-3">' +
                '<h3 class="card-title">' + (s.name || 'Sans nom') + '</h3>' +
                '<p class="text-xs mt-0.5" style="color:var(--color-muted-foreground)">' + (s.hostname || '') + '</p>' +
            '</div>' +
            statusBadge +
        '</div>' +
        '<p class="text-xs font-mono mb-3" style="color:var(--color-muted-foreground)">' + (s.ip_address || 'N/A') + '</p>' +
        getProgressBar('CPU', s.cpu_usage, cpuColor) +
        getProgressBar('RAM', s.ram_usage, ramColor) +
        getProgressBar('Disk', s.disk_usage, diskColor) +
        '<div class="flex items-center gap-2 mt-3 mb-3">' +
            '<span class="badge badge-outline">' + (s.os_type || 'N/A') + '</span>' +
            (s.location ? '<span class="text-xs" style="color:var(--color-muted-foreground)">' + s.location + '</span>' : '') +
        '</div>' +
        '<div class="separator"></div>' +
        '<div class="flex items-center justify-between mt-3">' +
            '<a href="/servers/' + s.id + '" class="text-xs font-medium" style="color:var(--color-primary)">Détails &rarr;</a>' +
            '<button onclick="detectAnomalies(' + s.id + ', \'' + (s.name || '').replace(/'/g, "\\'") + '\')" class="btn btn-sm btn-outline">Analyser IA</button>' +
        '</div>' +
    '</div>';
}

function renderPagination(data) {
    var container = document.getElementById('pagination');
    if (!data || data.last_page <= 1) { container.innerHTML = ''; return; }
    var html = '';
    var prevDisabled = data.current_page <= 1;
    html += '<button onclick="loadServers(' + (data.current_page - 1) + ')" ' + (prevDisabled ? 'disabled' : '') + ' class="btn btn-sm btn-outline" ' + (prevDisabled ? 'style="opacity:0.5;cursor:not-allowed"' : '') + '>&laquo;</button>';
    var start = Math.max(1, data.current_page - 2);
    var end = Math.min(data.last_page, data.current_page + 2);
    for (var i = start; i <= end; i++) {
        if (i === data.current_page) {
            html += '<button class="btn btn-sm btn-primary">' + i + '</button>';
        } else {
            html += '<button onclick="loadServers(' + i + ')" class="btn btn-sm btn-outline">' + i + '</button>';
        }
    }
    var nextDisabled = data.current_page >= data.last_page;
    html += '<button onclick="loadServers(' + (data.current_page + 1) + ')" ' + (nextDisabled ? 'disabled' : '') + ' class="btn btn-sm btn-outline" ' + (nextDisabled ? 'style="opacity:0.5;cursor:not-allowed"' : '') + '>&raquo;</button>';
    container.innerHTML = html;
}

async function loadServers(page) {
    if (page) currentPage = page;
    var search = document.getElementById('searchInput').value;
    var url = '/api/v1/servers?page=' + currentPage;
    if (search) url += '&search=' + encodeURIComponent(search);
    if (currentStatus) url += '&status=' + currentStatus;

    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('serversGrid').innerHTML = '';
    document.getElementById('emptyState').classList.add('hidden');

    try {
        var data = await apiCall(url);
        document.getElementById('loadingState').classList.add('hidden');

        if (!data.data || !data.data.length) {
            document.getElementById('emptyState').classList.remove('hidden');
            document.getElementById('pagination').innerHTML = '';
            return;
        }

        document.getElementById('serversGrid').innerHTML = data.data.map(renderServerCard).join('');
        renderPagination(data);
    } catch (e) {
        document.getElementById('loadingState').classList.add('hidden');
        showToast('Erreur', 'Impossible de charger les serveurs', 'error');
    }
}

async function syncZabbix() {
    var btn = document.getElementById('syncZabbixBtn');
    document.getElementById('syncIcon').classList.add('hidden');
    document.getElementById('syncSpinner').classList.remove('hidden');
    btn.disabled = true;
    try {
        var result = await apiCall('/api/v1/servers/sync-zabbix', { method: 'POST' });
        showToast('Synchronisation', result.message || 'Synchronisation Zabbix terminée', 'success');
        loadServers();
    } catch (e) {
        showToast('Erreur', 'Erreur lors de la synchronisation Zabbix', 'error');
    } finally {
        document.getElementById('syncIcon').classList.remove('hidden');
        document.getElementById('syncSpinner').classList.add('hidden');
        btn.disabled = false;
    }
}

async function detectAnomalies(id, name) {
    try {
        await apiCall('/api/v1/servers/' + id + '/detect-anomalies', { method: 'POST' });
        showToast('Analyse IA', 'Analyse lancée pour ' + name, 'info');
    } catch (e) {
        showToast('Erreur', "Erreur lors de l'analyse IA", 'error');
    }
}

function openAddModal() {
    var modal = document.getElementById('addModal');
    var content = document.getElementById('addModalContent');
    modal.classList.remove('hidden');
    setTimeout(function() { content.classList.add('show'); }, 10);
}

function closeAddModal() {
    var content = document.getElementById('addModalContent');
    content.classList.remove('show');
    setTimeout(function() {
        document.getElementById('addModal').classList.add('hidden');
        document.getElementById('addServerForm').reset();
        document.querySelectorAll('[id^="err_"]').forEach(function(el) { el.classList.add('hidden'); el.textContent = ''; });
    }, 300);
}

function closeModalOnBackdrop(event) {
    if (event.target === document.getElementById('addModal')) {
        closeAddModal();
    }
}

async function submitServer(e) {
    e.preventDefault();
    var payload = {
        name: document.getElementById('form_name').value.trim(),
        hostname: document.getElementById('form_hostname').value.trim(),
        ip_address: document.getElementById('form_ip_address').value.trim(),
        os_type: document.getElementById('form_os_type').value,
        location: document.getElementById('form_location').value.trim(),
        description: document.getElementById('form_description').value.trim()
    };

    document.querySelectorAll('[id^="err_"]').forEach(function(el) { el.classList.add('hidden'); el.textContent = ''; });

    var spinner = document.getElementById('submitSpinner');
    var btn = document.getElementById('submitBtn');
    spinner.classList.remove('hidden');
    btn.disabled = true;

    try {
        var result = await apiCall('/api/v1/servers', {
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
        showToast('Serveur ajouté', 'Le serveur a été créé avec succès', 'success');
        closeAddModal();
        loadServers();
    } catch (e) {
        if (e.message !== 'Unauthorized') {
            showToast('Erreur', "Erreur lors de l'ajout du serveur", 'error');
        }
    } finally {
        spinner.classList.add('hidden');
        btn.disabled = false;
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAddModal();
});

document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() { currentPage = 1; loadServers(); }, 300);
});

loadServers();
</script>
@endpush