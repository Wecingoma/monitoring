@extends('layouts.app')
@section('title', 'Incidents')
@section('subtitle', 'Gestion des incidents')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-3">
        <button onclick="openCreateModal()" class="btn btn-primary">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Créer un incident
        </button>
    </div>

    <div class="flex flex-wrap gap-2" id="severityPills">
        <span class="text-xs font-medium mr-1" style="color:var(--color-muted-foreground)">Sévérité :</span>
        <button onclick="filterSeverity('')" data-sev="" class="btn btn-sm btn-primary">Tous</button>
        <button onclick="filterSeverity('critical')" data-sev="critical" class="btn btn-sm btn-outline">Critique</button>
        <button onclick="filterSeverity('major')" data-sev="major" class="btn btn-sm btn-outline">Majeur</button>
        <button onclick="filterSeverity('minor')" data-sev="minor" class="btn btn-sm btn-outline">Mineur</button>
        <button onclick="filterSeverity('low')" data-sev="low" class="btn btn-sm btn-outline">Faible</button>
    </div>
    <div class="flex flex-wrap gap-2" id="statusPills">
        <span class="text-xs font-medium mr-1" style="color:var(--color-muted-foreground)">Statut :</span>
        <button onclick="filterStatus('')" data-sts="" class="btn btn-sm btn-primary">Tous</button>
        <button onclick="filterStatus('open')" data-sts="open" class="btn btn-sm btn-outline">Ouvert</button>
        <button onclick="filterStatus('investigating')" data-sts="investigating" class="btn btn-sm btn-outline">En investigation</button>
        <button onclick="filterStatus('identified')" data-sts="identified" class="btn btn-sm btn-outline">Identifié</button>
        <button onclick="filterStatus('monitoring')" data-sts="monitoring" class="btn btn-sm btn-outline">Surveillance</button>
        <button onclick="filterStatus('resolved')" data-sts="resolved" class="btn btn-sm btn-outline">Résolu</button>
    </div>

    <div id="incidentsGrid" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-full py-12 text-center" style="color:var(--color-muted-foreground)">Chargement...</div>
    </div>

    <div id="incidentPagination" class="flex justify-center items-center gap-2"></div>
</div>

<div id="createModal" class="fixed inset-0 z-50 hidden" onclick="if(event.target===this)closeCreateModal()">
    <div class="absolute inset-0" style="background:oklch(0 0 0 / 0.6);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px)"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div id="createModalContent" class="card w-full max-w-lg transform translate-y-8 opacity-0" style="transition:all 0.3s ease">
            <div class="flex items-center justify-between mb-6">
                <h3 class="card-title" style="font-size:1.125rem">Créer un incident</h3>
                <button onclick="closeCreateModal()" class="btn btn-ghost btn-icon" style="color:var(--color-muted-foreground)">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form id="createForm" onsubmit="createIncident(event)" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color:var(--color-foreground)">Titre <span style="color:var(--color-destructive)">*</span></label>
                    <input type="text" id="incTitle" required class="input" placeholder="Titre de l'incident">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color:var(--color-foreground)">Description</label>
                    <textarea id="incDesc" rows="3" class="input" style="height:auto;padding-top:0.5rem;padding-bottom:0.5rem;resize:none" placeholder="Description détaillée"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:var(--color-foreground)">Sévérité <span style="color:var(--color-destructive)">*</span></label>
                        <select id="incSeverity" class="select">
                            <option value="critical">Critique</option>
                            <option value="major">Majeur</option>
                            <option value="minor" selected>Mineur</option>
                            <option value="low">Faible</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:var(--color-foreground)">Date de début</label>
                        <input type="datetime-local" id="incStartedAt" class="input">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeCreateModal()" class="btn btn-outline">Annuler</button>
                    <button type="submit" id="createSubmitBtn" class="btn btn-primary">
                        <svg id="createSpinner" class="spinner w-4 h-4 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="resolveModal" class="fixed inset-0 z-50 hidden" onclick="if(event.target===this)closeResolveModal()">
    <div class="absolute inset-0" style="background:oklch(0 0 0 / 0.6);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px)"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div id="resolveModalContent" class="card w-full max-w-lg transform translate-y-8 opacity-0" style="transition:all 0.3s ease">
            <div class="flex items-center justify-between mb-6">
                <h3 class="card-title" style="font-size:1.125rem">Résoudre l'incident</h3>
                <button onclick="closeResolveModal()" class="btn btn-ghost btn-icon" style="color:var(--color-muted-foreground)">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form onsubmit="submitResolve(event)" class="space-y-4">
                <input type="hidden" id="resolveIncidentId">
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color:var(--color-foreground)">Résolution</label>
                    <textarea id="resolveResolution" rows="2" class="input" style="height:auto;padding-top:0.5rem;padding-bottom:0.5rem;resize:none" placeholder="Description de la résolution"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color:var(--color-foreground)">Cause racine</label>
                    <input type="text" id="resolveRootCause" class="input" placeholder="Cause racine identifiée">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeResolveModal()" class="btn btn-outline">Annuler</button>
                    <button type="submit" id="resolveSubmitBtn" class="btn btn-primary">
                        <svg id="resolveSpinner" class="spinner w-4 h-4 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Résoudre
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
    #createModalContent.show, #resolveModalContent.show { transform: translateY(0); opacity: 1; }
</style>
<script>
var currentSevFilter = '';
var currentStsFilter = '';
var currentPage = 1;

var severityBadge = {
    critical: { cls: 'badge badge-destructive', label: 'Critique' },
    major:    { cls: 'badge badge-warning', label: 'Majeur' },
    minor:    { cls: 'badge badge-info', label: 'Mineur' },
    low:      { cls: 'badge badge-secondary', label: 'Faible' }
};

var statusBadge = {
    open:         { cls: 'badge badge-destructive', label: 'Ouvert' },
    investigating:{ cls: 'badge badge-warning', label: 'En investigation' },
    identified:   { cls: 'badge badge-warning', label: 'Identifié' },
    monitoring:   { cls: 'badge badge-info', label: 'Surveillance' },
    resolved:     { cls: 'badge badge-success', label: 'Résolu' }
};

var severityBorderColor = {
    critical: 'oklch(0.55 0.2 25)',
    major:    'oklch(0.70 0.18 65)',
    minor:    'oklch(0.55 0.18 240)',
    low:      'oklch(0.55 0.2 260)'
};

function filterSeverity(val) {
    currentSevFilter = val;
    currentPage = 1;
    document.querySelectorAll('#severityPills .btn').forEach(function(b) {
        if (b.getAttribute('data-sev') === val) {
            b.className = 'btn btn-sm btn-primary';
        } else {
            b.className = 'btn btn-sm btn-outline';
        }
    });
    loadIncidents();
}

function filterStatus(val) {
    currentStsFilter = val;
    currentPage = 1;
    document.querySelectorAll('#statusPills .btn').forEach(function(b) {
        if (b.getAttribute('data-sts') === val) {
            b.className = 'btn btn-sm btn-primary';
        } else {
            b.className = 'btn btn-sm btn-outline';
        }
    });
    loadIncidents();
}

function openCreateModal() {
    var modal = document.getElementById('createModal');
    var content = document.getElementById('createModalContent');
    modal.classList.remove('hidden');
    setTimeout(function() { content.classList.add('show'); }, 10);
}

function closeCreateModal() {
    var content = document.getElementById('createModalContent');
    content.classList.remove('show');
    setTimeout(function() {
        document.getElementById('createModal').classList.add('hidden');
    }, 300);
}

function openResolveModal(id) {
    document.getElementById('resolveIncidentId').value = id;
    document.getElementById('resolveResolution').value = '';
    document.getElementById('resolveRootCause').value = '';
    var modal = document.getElementById('resolveModal');
    var content = document.getElementById('resolveModalContent');
    modal.classList.remove('hidden');
    setTimeout(function() { content.classList.add('show'); }, 10);
}

function closeResolveModal() {
    var content = document.getElementById('resolveModalContent');
    content.classList.remove('show');
    setTimeout(function() {
        document.getElementById('resolveModal').classList.add('hidden');
    }, 300);
}

async function submitResolve(e) {
    e.preventDefault();
    var id = document.getElementById('resolveIncidentId').value;
    var resolution = document.getElementById('resolveResolution').value;
    var rootCause = document.getElementById('resolveRootCause').value;

    var spinner = document.getElementById('resolveSpinner');
    var btn = document.getElementById('resolveSubmitBtn');
    spinner.classList.remove('hidden');
    btn.disabled = true;

    try {
        await apiCall('/api/v1/incidents/' + id + '/resolve', {
            method: 'POST',
            body: JSON.stringify({ resolution: resolution, root_cause: rootCause })
        });
        showToast('Incident résolu', 'Incident résolu avec succès', 'success');
        closeResolveModal();
        loadIncidents(currentPage);
    } catch (e) {
        showToast('Erreur', 'Erreur lors de la résolution', 'error');
    } finally {
        spinner.classList.add('hidden');
        btn.disabled = false;
    }
}

async function createIncident(e) {
    e.preventDefault();
    var title = document.getElementById('incTitle').value.trim();
    var description = document.getElementById('incDesc').value.trim();
    var severity = document.getElementById('incSeverity').value;
    var startedAt = document.getElementById('incStartedAt').value;

    if (!title) { showToast('Erreur', 'Le titre est requis', 'warning'); return; }

    var spinner = document.getElementById('createSpinner');
    var btn = document.getElementById('createSubmitBtn');
    spinner.classList.remove('hidden');
    btn.disabled = true;

    try {
        await apiCall('/api/v1/incidents', {
            method: 'POST',
            body: JSON.stringify({ title: title, description: description, severity: severity, started_at: startedAt })
        });
        showToast('Incident créé', 'Incident créé avec succès', 'success');
        closeCreateModal();
        document.getElementById('incTitle').value = '';
        document.getElementById('incDesc').value = '';
        loadIncidents(1);
    } catch (e) {
        showToast('Erreur', 'Erreur lors de la création', 'error');
    } finally {
        spinner.classList.add('hidden');
        btn.disabled = false;
    }
}

function renderIncidentCard(i) {
    var sb = severityBadge[i.severity] || { cls: 'badge badge-secondary', label: i.severity };
    var stb = statusBadge[i.status] || { cls: 'badge badge-secondary', label: i.status };
    var blc = severityBorderColor[i.severity] || severityBorderColor.low;

    var avatarInitial = i.assigned_to ? i.assigned_to.name.charAt(0).toUpperCase() : '?';
    var avatarBg = i.assigned_to ? 'style="background:linear-gradient(135deg, oklch(0.55 0.2 260), oklch(0.55 0.2 290));color:var(--color-primary-foreground)"' : 'style="background:var(--color-secondary);color:var(--color-muted-foreground)"';
    var assignedName = i.assigned_to ? i.assigned_to.name : 'Non assigné';

    var actions = '';
    if (i.status !== 'resolved') {
        actions = '<button onclick="openResolveModal(' + i.id + ')" class="btn btn-sm btn-primary">Résoudre</button>';
    }

    return '<div class="card" style="border-left:4px solid ' + blc + '">' +
        '<div class="flex items-start justify-between gap-3 mb-3">' +
            '<div class="flex items-center gap-2 flex-wrap">' +
                '<span class="' + sb.cls + '">' + sb.label + '</span>' +
                '<span class="' + stb.cls + '">' + stb.label + '</span>' +
            '</div>' +
            actions +
        '</div>' +
        '<h3 class="card-title mb-1">' + i.title + '</h3>' +
        (i.description ? '<p class="text-sm mb-3" style="color:var(--color-muted-foreground);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">' + i.description + '</p>' : '') +
        '<div class="flex items-center justify-between text-xs" style="color:var(--color-muted-foreground)">' +
            '<div class="flex items-center gap-2">' +
                '<div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0" ' + avatarBg + '>' + avatarInitial + '</div>' +
                '<span>' + assignedName + '</span>' +
            '</div>' +
            '<span>' + (i.started_at || '') + '</span>' +
        '</div>' +
    '</div>';
}

async function loadIncidents(page) {
    currentPage = page || 1;
    var url = '/api/v1/incidents?page=' + currentPage;
    if (currentSevFilter) url += '&severity=' + currentSevFilter;
    if (currentStsFilter) url += '&status=' + currentStsFilter;

    var grid = document.getElementById('incidentsGrid');
    grid.innerHTML = '<div class="col-span-full py-12 text-center" style="color:var(--color-muted-foreground)">Chargement...</div>';

    try {
        var data = await apiCall(url);
        if (!data.data || !data.data.length) {
            grid.innerHTML = '<div class="col-span-full py-12 text-center">' +
                '<div class="flex flex-col items-center justify-center">' +
                    '<div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background:var(--color-secondary)">' +
                        '<svg class="w-8 h-8" style="color:var(--color-muted-foreground)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>' +
                    '</div>' +
                    '<p style="color:var(--color-muted-foreground)">Aucun incident trouvé</p>' +
                '</div>' +
            '</div>';
            document.getElementById('incidentPagination').innerHTML = '';
            return;
        }
        grid.innerHTML = data.data.map(renderIncidentCard).join('');

        var pagDiv = document.getElementById('incidentPagination');
        if (data.last_page && data.last_page > 1) {
            var phtml = '';
            var prevDisabled = data.current_page <= 1;
            phtml += '<button onclick="loadIncidents(' + (data.current_page - 1) + ')" ' + (prevDisabled ? 'disabled' : '') + ' class="btn btn-sm btn-outline" ' + (prevDisabled ? 'style="opacity:0.5;cursor:not-allowed"' : '') + '>&laquo;</button>';
            var start = Math.max(1, data.current_page - 2);
            var end = Math.min(data.last_page, data.current_page + 2);
            for (var p = start; p <= end; p++) {
                if (p === data.current_page) {
                    phtml += '<button class="btn btn-sm btn-primary">' + p + '</button>';
                } else {
                    phtml += '<button onclick="loadIncidents(' + p + ')" class="btn btn-sm btn-outline">' + p + '</button>';
                }
            }
            var nextDisabled = data.current_page >= data.last_page;
            phtml += '<button onclick="loadIncidents(' + (data.current_page + 1) + ')" ' + (nextDisabled ? 'disabled' : '') + ' class="btn btn-sm btn-outline" ' + (nextDisabled ? 'style="opacity:0.5;cursor:not-allowed"' : '') + '>&raquo;</button>';
            pagDiv.innerHTML = phtml;
        } else {
            pagDiv.innerHTML = '';
        }
    } catch (e) {
        grid.innerHTML = '<div class="col-span-full py-12 text-center" style="color:var(--color-muted-foreground)">Erreur de chargement</div>';
        showToast('Erreur', 'Impossible de charger les incidents', 'error');
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateModal();
        closeResolveModal();
    }
});

loadIncidents(1);
</script>
@endpush