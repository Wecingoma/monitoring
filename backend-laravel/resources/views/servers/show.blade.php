@extends('layouts.app')
@section('title', 'Détails Serveur')
@section('subtitle', 'Informations serveur')

@section('content')
<div class="space-y-6">

    <nav class="flex items-center gap-2 text-sm" id="breadcrumb">
        <a href="/servers" class="text-muted-foreground hover:text-foreground transition-colors">Serveurs</a>
        <svg class="w-4 h-4 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
        <span class="text-foreground font-medium" id="breadcrumbServerName">Chargement...</span>
    </nav>

    <div id="serverDetail" class="space-y-6">
        <div class="flex items-center justify-center py-12">
            <svg class="spinner w-8 h-8" style="color:var(--color-primary)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<style>
    @keyframes spin { to { transform: rotate(360deg); } }
    .spinner { animation: spin 0.7s linear infinite; }
    .progress-purple .progress-bar { background: oklch(0.55 0.2 290); }
    .metric-card { background: var(--color-card); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 1rem 1.25rem; transition: border-color 0.15s ease; }
    .metric-card:hover { border-color: oklch(0.35 0.02 260); }
</style>
<script>
var serverId = {{ $serverId }};

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

function metricCard(label, value, unit, progressColor) {
    var v = parseFloat(value) || 0;
    var barColor = progressColor;
    if (v > 90) barColor = 'progress-red';
    else if (v > 75) barColor = 'progress-amber';

    var textColor = 'color:var(--color-foreground)';
    if (v > 90) textColor = 'color:var(--color-destructive)';
    else if (v > 75) textColor = 'color:var(--color-warning)';

    return '<div class="metric-card">' +
        '<p class="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-2">' + label + '</p>' +
        '<p class="text-2xl font-bold" style="' + textColor + '">' + v + '<span class="text-sm font-normal text-muted-foreground ml-0.5">' + unit + '</span></p>' +
        '<div class="progress ' + barColor + '" style="margin-top:0.5rem">' +
            '<div class="progress-bar" style="width:' + Math.min(v, 100) + '%"></div>' +
        '</div>' +
    '</div>';
}

function renderAlertMini(a) {
    var sevMap = {
        critical: { cls: 'badge badge-destructive', label: 'Critique' },
        warning:  { cls: 'badge badge-warning', label: 'Warning' },
        info:     { cls: 'badge badge-info', label: 'Info' }
    };
    var sev = sevMap[a.severity] || sevMap.info;
    var dotColor = { critical: 'oklch(0.55 0.2 25)', warning: 'oklch(0.70 0.18 65)', info: 'oklch(0.55 0.18 240)' };
    var dc = dotColor[a.severity] || dotColor.info;

    return '<div class="card" style="padding:0.75rem 1rem">' +
        '<div class="flex items-start gap-3">' +
            '<span style="width:8px;height:8px;border-radius:9999px;background:' + dc + ';flex-shrink:0;margin-top:6px;display:inline-block"></span>' +
            '<div class="flex-1 min-w-0">' +
                '<p class="text-sm text-foreground truncate">' + (a.title || a.name || 'Alerte') + '</p>' +
                '<div class="flex items-center gap-2 mt-1">' +
                    '<span class="' + sev.cls + '">' + sev.label + '</span>' +
                    '<span class="text-xs text-muted-foreground">' + (a.created_at || '') + '</span>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>';
}

function renderAnomalyMini(a) {
    var sevMap = {
        critical: { cls: 'badge badge-destructive', label: 'Critique' },
        warning:  { cls: 'badge badge-warning', label: 'Warning' },
        low:      { cls: 'badge badge-info', label: 'Faible' }
    };
    var sev = sevMap[a.severity] || sevMap.low;

    return '<div class="card" style="padding:0.75rem 1rem">' +
        '<div class="flex items-start gap-3">' +
            '<svg class="w-4 h-4 flex-shrink-0 mt-0.5" style="color:oklch(0.55 0.2 290)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18H10a3.374 3.374 0 01-.429-1.547l-.548-.547z"/></svg>' +
            '<div class="flex-1 min-w-0">' +
                '<div class="flex items-center gap-2">' +
                    '<span class="badge badge-info">' + (a.type || 'N/A') + '</span>' +
                    '<span class="' + sev.cls + '">' + sev.label + '</span>' +
                    '<span class="text-xs font-mono text-muted-foreground">Score: ' + (a.score ? a.score.toFixed(2) : '0.00') + '</span>' +
                '</div>' +
                '<p class="text-sm text-foreground mt-1">' + (a.description || 'Anomalie détectée') + '</p>' +
                '<p class="text-xs text-muted-foreground mt-0.5">' + (a.detected_at || '') + '</p>' +
            '</div>' +
        '</div>' +
    '</div>';
}

async function loadServerDetail() {
    try {
        var s = await apiCall('/api/v1/servers/' + serverId);

        document.getElementById('breadcrumbServerName').textContent = s.name || 'Serveur';

        var infoRows = function(label, value) {
            return '<div class="flex justify-between py-2" style="border-bottom:1px solid var(--color-border)">' +
                '<span class="text-xs text-muted-foreground">' + label + '</span>' +
                '<span class="text-sm text-foreground font-mono">' + (value || 'N/A') + '</span>' +
            '</div>';
        };

        document.getElementById('serverDetail').innerHTML =
            '<div class="grid grid-cols-1 lg:grid-cols-5 gap-4">' +
                '<div class="lg:col-span-2 card">' +
                    '<div class="flex items-start justify-between mb-4">' +
                        '<div class="flex items-center gap-3">' +
                            '<div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background:oklch(0.55 0.2 260 / 0.12)">' +
                                '<svg class="w-5 h-5" style="color:oklch(0.55 0.2 260)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><circle cx="6" cy="6" r="1" fill="currentColor"/><circle cx="6" cy="18" r="1" fill="currentColor"/></svg>' +
                            '</div>' +
                            '<div>' +
                                '<h3 class="card-title">' + s.name + '</h3>' +
                                '<p class="text-xs text-muted-foreground">' + (s.hostname || '') + '</p>' +
                            '</div>' +
                        '</div>' +
                        getStatusBadge(s.status) +
                    '</div>' +
                    '<div class="space-y-0">' +
                        infoRows('Adresse IP', s.ip_address) +
                        infoRows('Système', (s.os_type || 'N/A')) +
                        infoRows('Localisation', (s.location || 'N/A')) +
                        infoRows('Uptime', (s.uptime || 'N/A')) +
                        infoRows('Dernier check', (s.last_check_at || 'N/A')) +
                    '</div>' +
                '</div>' +

                '<div class="lg:col-span-3 grid grid-cols-2 gap-4">' +
                    metricCard('CPU', s.cpu_usage, '%', 'progress-blue') +
                    metricCard('RAM', s.ram_usage, '%', 'progress-green') +
                    metricCard('Disque', s.disk_usage, '%', 'progress-amber') +
                    metricCard('Réseau', s.network_usage, '%', 'progress-purple') +
                '</div>' +
            '</div>' +

            '<div class="flex items-center gap-3">' +
                '<button onclick="syncZabbix()" class="btn btn-outline" id="syncBtn">' +
                    '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0118.8-4.3M22 12.5a10 10 0 01-18.8 4.3"/></svg>' +
                    'Synchroniser Zabbix' +
                '</button>' +
                '<button onclick="analyzeAI()" class="btn btn-primary" id="aiBtn">' +
                    '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18H10a3.374 3.374 0 01-.429-1.547l-.548-.547z"/></svg>' +
                    'Analyser IA' +
                '</button>' +
            '</div>' +

            '<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">' +
                '<div>' +
                    '<div class="flex items-center justify-between mb-3">' +
                        '<h4 class="card-title">Alertes récentes</h4>' +
                        '<a href="/alerts?server_id=' + serverId + '" class="text-xs text-muted-foreground hover:text-foreground transition-colors">Voir tout &rarr;</a>' +
                    '</div>' +
                    '<div id="serverAlerts" class="space-y-2">' +
                        '<div class="flex items-center justify-center py-8"><svg class="spinner w-6 h-6" style="color:var(--color-primary)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg></div>' +
                    '</div>' +
                '</div>' +
                '<div>' +
                    '<div class="flex items-center justify-between mb-3">' +
                        '<h4 class="card-title">Anomalies récentes</h4>' +
                        '<a href="/anomalies?server_id=' + serverId + '" class="text-xs text-muted-foreground hover:text-foreground transition-colors">Voir tout &rarr;</a>' +
                    '</div>' +
                    '<div id="serverAnomalies" class="space-y-2">' +
                        '<div class="flex items-center justify-center py-8"><svg class="spinner w-6 h-6" style="color:var(--color-primary)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg></div>' +
                    '</div>' +
                '</div>' +
            '</div>';

        loadServerAlerts();
        loadServerAnomalies();
    } catch (e) {
        document.getElementById('serverDetail').innerHTML =
            '<div class="card text-center py-12">' +
                '<svg class="w-12 h-12 mx-auto mb-4" style="color:var(--color-destructive)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>' +
                '<p class="text-foreground font-medium">Erreur lors du chargement du serveur</p>' +
                '<p class="text-muted-foreground text-sm mt-1">Vérifiez que le serveur existe</p>' +
            '</div>';
    }
}

async function loadServerAlerts() {
    var container = document.getElementById('serverAlerts');
    try {
        var data = await apiCall('/api/v1/alerts?server_id=' + serverId + '&per_page=5');
        var alerts = data.data || [];
        if (!alerts.length) {
            container.innerHTML = '<p class="text-muted-foreground text-sm text-center py-8">Aucune alerte</p>';
            return;
        }
        container.innerHTML = alerts.map(renderAlertMini).join('');
    } catch (e) {
        container.innerHTML = '<p class="text-muted-foreground text-sm text-center py-8">Erreur de chargement</p>';
    }
}

async function loadServerAnomalies() {
    var container = document.getElementById('serverAnomalies');
    try {
        var data = await apiCall('/api/v1/anomalies?server_id=' + serverId + '&per_page=5');
        var anomalies = data.data || [];
        if (!anomalies.length) {
            container.innerHTML = '<p class="text-muted-foreground text-sm text-center py-8">Aucune anomalie</p>';
            return;
        }
        container.innerHTML = anomalies.map(renderAnomalyMini).join('');
    } catch (e) {
        container.innerHTML = '<p class="text-muted-foreground text-sm text-center py-8">Erreur de chargement</p>';
    }
}

async function syncZabbix() {
    var btn = document.getElementById('syncBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="spinner w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Synchronisation...';
    try {
        await apiCall('/api/v1/servers/' + serverId + '/sync-metrics', { method: 'POST' });
        showToast('Synchronisation', 'Métriques Zabbix synchronisées avec succès', 'success');
        loadServerDetail();
    } catch (e) {
        showToast('Erreur', 'Erreur lors de la synchronisation Zabbix', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0118.8-4.3M22 12.5a10 10 0 01-18.8 4.3"/></svg> Synchroniser Zabbix';
    }
}

async function analyzeAI() {
    var btn = document.getElementById('aiBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="spinner w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Analyse en cours...';
    try {
        await apiCall('/api/v1/servers/' + serverId + '/detect-anomalies', { method: 'POST' });
        showToast('Analyse IA', 'Analyse IA terminée avec succès', 'success');
        loadServerAnomalies();
    } catch (e) {
        showToast('Erreur', "Erreur lors de l'analyse IA", 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18H10a3.374 3.374 0 01-.429-1.547l-.548-.547z"/></svg> Analyser IA';
    }
}

loadServerDetail();
</script>
@endpush