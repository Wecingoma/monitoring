@extends('layouts.app')
@section('title', 'Anomalies IA')
@section('subtitle', "Détection d'anomalies par intelligence artificielle")

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full sm:w-auto">
            <div class="flex items-center gap-2 flex-wrap" id="severityFilters">
                <span class="text-xs font-medium mr-1" style="color:var(--color-muted-foreground)">Sévérité :</span>
                <button onclick="setFilter('severity','')" class="btn btn-sm btn-primary" data-group="severity" data-value="">Toutes</button>
                <button onclick="setFilter('severity','critical')" class="btn btn-sm btn-outline" data-group="severity" data-value="critical">Critique</button>
                <button onclick="setFilter('severity','warning')" class="btn btn-sm btn-outline" data-group="severity" data-value="warning">Warning</button>
                <button onclick="setFilter('severity','low')" class="btn btn-sm btn-outline" data-group="severity" data-value="low">Faible</button>
            </div>
            <div class="flex items-center gap-2 flex-wrap" id="typeFilters">
                <span class="text-xs font-medium mr-1" style="color:var(--color-muted-foreground)">Type :</span>
                <button onclick="setFilter('type','')" class="btn btn-sm btn-primary" data-group="type" data-value="">Tous</button>
                <button onclick="setFilter('type','cpu')" class="btn btn-sm btn-outline" data-group="type" data-value="cpu">CPU</button>
                <button onclick="setFilter('type','ram')" class="btn btn-sm btn-outline" data-group="type" data-value="ram">RAM</button>
                <button onclick="setFilter('type','network')" class="btn btn-sm btn-outline" data-group="type" data-value="network">Réseau</button>
                <button onclick="setFilter('type','disk')" class="btn btn-sm btn-outline" data-group="type" data-value="disk">Disque</button>
                <button onclick="setFilter('type','behavior')" class="btn btn-sm btn-outline" data-group="type" data-value="behavior">Comportement</button>
                <button onclick="setFilter('type','security')" class="btn btn-sm btn-outline" data-group="type" data-value="security">Sécurité</button>
            </div>
        </div>
        <button id="detectBtn" onclick="runDetection()" class="btn btn-primary">
            <svg id="detectIcon" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18H10a3.374 3.374 0 01-.429-1.547l-.548-.547z"/></svg>
            <svg id="detectSpinner" class="spinner w-4 h-4 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            Lancer la détection IA
        </button>
    </div>

    <div id="anomaliesGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-4"></div>

    <div id="emptyState" class="hidden">
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-4" style="background:var(--color-secondary)">
                <svg class="w-10 h-10" style="color:var(--color-muted-foreground)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18H10a3.374 3.374 0 01-.429-1.547l-.548-.547z"/></svg>
            </div>
            <p class="text-lg font-medium" style="color:var(--color-foreground)">Aucune anomalie détectée</p>
            <p class="text-sm mt-1" style="color:var(--color-muted-foreground)">Lancez une détection IA pour analyser les serveurs</p>
        </div>
    </div>

    <div id="loadingState" class="hidden">
        <div class="flex items-center justify-center py-16">
            <svg class="spinner w-8 h-8" style="color:var(--color-warning)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        </div>
    </div>

    <div id="pagination" class="flex justify-center items-center gap-2"></div>
</div>
@endsection

@push('scripts')
<style>
    @keyframes spin { to { transform: rotate(360deg); } }
    .spinner { animation: spin 0.7s linear infinite; }
</style>
<script>
var filters = { severity: '', type: '' };
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
    loadAnomalies();
}

function severityBadge(s) {
    var map = {
        critical: { cls: 'badge badge-destructive', label: 'Critique' },
        warning:  { cls: 'badge badge-warning', label: 'Warning' },
        low:      { cls: 'badge badge-info', label: 'Faible' }
    };
    var c = map[s] || map.low;
    return '<span class="' + c.cls + '">' + c.label + '</span>';
}

function typeBadge(t) {
    return '<span class="badge badge-info">' + (t || 'N/A') + '</span>';
}

function scoreDisplay(score) {
    var pct = Math.round((score || 0) * 100);
    var color;
    if (score > 0.7) color = 'var(--color-destructive)';
    else if (score > 0.4) color = 'var(--color-warning)';
    else color = 'var(--color-success)';
    return '<span style="font-weight:700;font-size:1.125rem;color:' + color + '">Score: ' + (score ? score.toFixed(2) : '0.00') + '</span>';
}

function severityBorderColor(s) {
    var map = {
        critical: 'oklch(0.55 0.2 25)',
        warning:  'oklch(0.70 0.18 65)',
        low:      'oklch(0.55 0.18 240)'
    };
    return map[s] || map.low;
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

function renderAnomalyCard(a) {
    var blc = severityBorderColor(a.severity);
    var serverName = (a.server && a.server.name) ? a.server.name : 'N/A';

    return '<div class="card" style="border-left:4px solid ' + blc + '">' +
        '<div class="flex items-center gap-2 mb-2 flex-wrap">' +
            typeBadge(a.type) +
            severityBadge(a.severity) +
        '</div>' +
        scoreDisplay(a.score) +
        '<p class="font-medium mt-2 mb-1" style="color:var(--color-foreground)">' + (a.description || 'Aucune description') + '</p>' +
        (a.recommendation ? '<p class="text-sm" style="color:var(--color-muted-foreground)"><span class="font-medium">Recommandation :</span> ' + a.recommendation + '</p>' : '') +
        '<div class="flex items-center gap-2 text-xs mt-3" style="color:var(--color-muted-foreground)">' +
            '<span>' + serverName + '</span>' +
            '<span>&middot;</span>' +
            '<span>' + timeAgo(a.detected_at) + '</span>' +
        '</div>' +
        '<div class="separator mt-3 mb-3"></div>' +
        '<div class="flex items-center justify-end">' +
            '<button onclick="markFalsePositive(' + a.id + ')" class="btn btn-sm btn-outline">Marquer comme faux positif</button>' +
        '</div>' +
    '</div>';
}

function renderPagination(data) {
    var container = document.getElementById('pagination');
    if (!data || data.last_page <= 1) { container.innerHTML = ''; return; }
    var html = '';
    var prevDisabled = data.current_page <= 1;
    html += '<button onclick="loadAnomalies(' + (data.current_page - 1) + ')" ' + (prevDisabled ? 'disabled' : '') + ' class="btn btn-sm btn-outline" ' + (prevDisabled ? 'style="opacity:0.5;cursor:not-allowed"' : '') + '>&laquo;</button>';
    var start = Math.max(1, data.current_page - 2);
    var end = Math.min(data.last_page, data.current_page + 2);
    for (var i = start; i <= end; i++) {
        if (i === data.current_page) {
            html += '<button class="btn btn-sm btn-primary">' + i + '</button>';
        } else {
            html += '<button onclick="loadAnomalies(' + i + ')" class="btn btn-sm btn-outline">' + i + '</button>';
        }
    }
    var nextDisabled = data.current_page >= data.last_page;
    html += '<button onclick="loadAnomalies(' + (data.current_page + 1) + ')" ' + (nextDisabled ? 'disabled' : '') + ' class="btn btn-sm btn-outline" ' + (nextDisabled ? 'style="opacity:0.5;cursor:not-allowed"' : '') + '>&raquo;</button>';
    container.innerHTML = html;
}

async function loadAnomalies(page) {
    if (page) currentPage = page;
    var url = '/api/v1/anomalies?page=' + currentPage;
    if (filters.severity) url += '&severity=' + filters.severity;
    if (filters.type) url += '&type=' + filters.type;

    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('anomaliesGrid').innerHTML = '';
    document.getElementById('emptyState').classList.add('hidden');

    try {
        var data = await apiCall(url);
        document.getElementById('loadingState').classList.add('hidden');
        if (!data.data || !data.data.length) {
            document.getElementById('emptyState').classList.remove('hidden');
            document.getElementById('pagination').innerHTML = '';
            return;
        }
        document.getElementById('anomaliesGrid').innerHTML = data.data.map(renderAnomalyCard).join('');
        renderPagination(data);
    } catch (e) {
        document.getElementById('loadingState').classList.add('hidden');
        showToast('Erreur', 'Impossible de charger les anomalies', 'error');
    }
}

async function runDetection() {
    var btn = document.getElementById('detectBtn');
    document.getElementById('detectIcon').classList.add('hidden');
    document.getElementById('detectSpinner').classList.remove('hidden');
    btn.disabled = true;
    try {
        await apiCall('/api/v1/anomalies/detect', { method: 'POST' });
        showToast('Détection IA', 'Détection IA lancée sur tous les serveurs', 'success');
        loadAnomalies();
    } catch (e) {
        showToast('Erreur', 'Erreur lors du lancement de la détection IA', 'error');
    } finally {
        document.getElementById('detectIcon').classList.remove('hidden');
        document.getElementById('detectSpinner').classList.add('hidden');
        btn.disabled = false;
    }
}

function markFalsePositive(id) {
    if (!confirm('Marquer cette anomalie comme faux positif ?')) return;
    doMarkFalsePositive(id);
}

async function doMarkFalsePositive(id) {
    try {
        await apiCall('/api/v1/anomalies/' + id + '/false-positive', { method: 'POST' });
        showToast('Faux positif', 'Anomalie marquée comme faux positif', 'success');
        loadAnomalies();
    } catch (e) {
        showToast('Erreur', 'Erreur lors du marquage', 'error');
    }
}

loadAnomalies();
</script>
@endpush