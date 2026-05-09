@extends('layouts.app')
@section('title', 'Logs')
@section('subtitle', 'Consultation des logs système')

@section('content')
<div class="space-y-6">
    <div id="logStats" class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3"></div>

    <div class="flex flex-wrap gap-2" id="levelFilters">
        <button onclick="filterLevel('')" data-lvl="" class="btn btn-sm btn-primary">Tous</button>
        <button onclick="filterLevel('emergency')" data-lvl="emergency" class="btn btn-sm btn-outline">Emergency</button>
        <button onclick="filterLevel('critical')" data-lvl="critical" class="btn btn-sm btn-outline">Critical</button>
        <button onclick="filterLevel('error')" data-lvl="error" class="btn btn-sm btn-outline">Error</button>
        <button onclick="filterLevel('warning')" data-lvl="warning" class="btn btn-sm btn-outline">Warning</button>
        <button onclick="filterLevel('notice')" data-lvl="notice" class="btn btn-sm btn-outline">Notice</button>
        <button onclick="filterLevel('info')" data-lvl="info" class="btn btn-sm btn-outline">Info</button>
        <button onclick="filterLevel('debug')" data-lvl="debug" class="btn btn-sm btn-outline">Debug</button>
    </div>

    <div class="flex gap-3">
        <input type="text" id="searchQuery" class="input" style="flex:1;font-family:monospace" placeholder="Recherche Elasticsearch...">
        <button onclick="elasticSearch(1)" class="btn btn-primary">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Rechercher
        </button>
    </div>

    <div id="logsList" class="space-y-2">
        <div class="py-12 text-center" style="color:var(--color-muted-foreground)">Chargement...</div>
    </div>

    <div id="logsPagination" class="flex justify-center items-center gap-2"></div>
</div>
@endsection

@push('scripts')
<script>
var currentLevel = '';
var currentPage = 1;
var isElasticSearch = false;

var levelBadge = {
    emergency: { cls: 'badge badge-destructive', label: 'EMERGENCY' },
    alert:     { cls: 'badge badge-destructive', label: 'ALERT' },
    critical:  { cls: 'badge badge-destructive', label: 'CRITICAL' },
    error:     { cls: 'badge badge-destructive', label: 'ERROR' },
    warning:   { cls: 'badge badge-warning', label: 'WARNING' },
    notice:    { cls: 'badge badge-info', label: 'NOTICE' },
    info:      { cls: 'badge badge-success', label: 'INFO' },
    debug:     { cls: 'badge badge-secondary', label: 'DEBUG' }
};

function filterLevel(val) {
    currentLevel = val;
    isElasticSearch = false;
    document.querySelectorAll('#levelFilters .btn').forEach(function(b) {
        if (b.getAttribute('data-lvl') === val) {
            b.className = 'btn btn-sm btn-primary';
        } else {
            b.className = 'btn btn-sm btn-outline';
        }
    });
    loadLogs(1);
}

async function loadLogStats() {
    try {
        var data = await apiCall('/api/v1/logs/stats');
        var container = document.getElementById('logStats');
        var stats = data.data || data;
        var html = '';
        var levels = ['emergency','alert','critical','error','warning','notice','info','debug'];
        levels.forEach(function(lvl) {
            var count = stats[lvl] || 0;
            var lb = levelBadge[lvl] || levelBadge.debug;
            html += '<div class="card stat-card animate-in" style="padding:0.75rem 1rem">' +
                '<div class="flex items-center justify-between gap-2">' +
                    '<span class="text-xs font-medium" style="color:var(--color-muted-foreground)">' + lvl.toUpperCase() + '</span>' +
                    '<span class="text-lg font-bold" style="color:var(--color-foreground)">' + count + '</span>' +
                '</div>' +
            '</div>';
        });
        container.innerHTML = html;
    } catch(e) {}
}

function renderLogEntry(l) {
    var lb = levelBadge[l.level] || levelBadge.debug;
    return '<div class="card" style="padding:0.75rem 1rem">' +
        '<div class="flex items-center gap-2 mb-1 flex-wrap">' +
            '<span class="' + lb.cls + '">' + lb.label + '</span>' +
            (l.source ? '<span class="badge badge-outline">' + l.source + '</span>' : '') +
            (l.server ? '<span class="text-xs" style="color:var(--color-muted-foreground)">' + (l.server.name || l.server) + '</span>' : '') +
            '<span class="text-xs ml-auto" style="color:var(--color-muted-foreground)">' + (l.logged_at || l.created_at || '') + '</span>' +
        '</div>' +
        '<p class="font-mono text-sm" style="color:var(--color-foreground)">' + (l.message || '') + '</p>' +
    '</div>';
}

async function loadLogs(page) {
    currentPage = page;
    isElasticSearch = false;
    var url = '/api/v1/logs?page=' + page;
    if (currentLevel) url += '&level=' + currentLevel;

    var container = document.getElementById('logsList');
    container.innerHTML = '<div class="py-12 text-center" style="color:var(--color-muted-foreground)">Chargement...</div>';

    try {
        var data = await apiCall(url);
        renderLogs(data);
    } catch(e) {
        container.innerHTML = '<div class="py-12 text-center" style="color:var(--color-muted-foreground)">Erreur de chargement</div>';
        showToast('Erreur', 'Impossible de charger les logs', 'error');
    }
}

async function elasticSearch(page) {
    var query = document.getElementById('searchQuery').value;
    if (!query) { showToast('Erreur', 'Entrez un terme de recherche', 'warning'); return; }
    currentPage = page;
    isElasticSearch = true;

    var container = document.getElementById('logsList');
    container.innerHTML = '<div class="py-12 text-center" style="color:var(--color-muted-foreground)">Recherche...</div>';

    try {
        var data = await apiCall('/api/v1/logs/search-elastic', {
            method: 'POST',
            body: JSON.stringify({ query: query, page: page })
        });
        renderLogs(data);
    } catch(e) {
        container.innerHTML = '<div class="py-12 text-center" style="color:var(--color-muted-foreground)">Erreur de recherche</div>';
        showToast('Erreur', 'Erreur de recherche Elasticsearch', 'error');
    }
}

function renderLogs(data) {
    var container = document.getElementById('logsList');
    var entries = data.data || [];
    if (!entries.length) {
        container.innerHTML = '<div class="flex flex-col items-center justify-center py-16 text-center">' +
            '<div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background:var(--color-secondary)">' +
                '<svg class="w-8 h-8" style="color:var(--color-muted-foreground)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>' +
            '</div>' +
            '<p class="text-lg font-medium" style="color:var(--color-foreground)">Aucun log trouvé</p>' +
            '<p class="text-sm mt-1" style="color:var(--color-muted-foreground)">Modifiez les filtres ou la recherche</p>' +
        '</div>';
        document.getElementById('logsPagination').innerHTML = '';
        return;
    }

    container.innerHTML = entries.map(renderLogEntry).join('');

    var pagDiv = document.getElementById('logsPagination');
    if (data.last_page && data.last_page > 1) {
        var phtml = '';
        var prevDisabled = data.current_page <= 1;
        var clickPrev = isElasticSearch ? 'elasticSearch(' + (data.current_page - 1) + ')' : 'loadLogs(' + (data.current_page - 1) + ')';
        phtml += '<button onclick="' + clickPrev + '" ' + (prevDisabled ? 'disabled' : '') + ' class="btn btn-sm btn-outline" ' + (prevDisabled ? 'style="opacity:0.5;cursor:not-allowed"' : '') + '>&laquo;</button>';
        var start = Math.max(1, data.current_page - 2);
        var end = Math.min(data.last_page, data.current_page + 2);
        for (var p = start; p <= end; p++) {
            var clickFn = isElasticSearch ? 'elasticSearch(' + p + ')' : 'loadLogs(' + p + ')';
            if (p === data.current_page) {
                phtml += '<button class="btn btn-sm btn-primary">' + p + '</button>';
            } else {
                phtml += '<button onclick="' + clickFn + '" class="btn btn-sm btn-outline">' + p + '</button>';
            }
        }
        var nextDisabled = data.current_page >= data.last_page;
        var clickNext = isElasticSearch ? 'elasticSearch(' + (data.current_page + 1) + ')' : 'loadLogs(' + (data.current_page + 1) + ')';
        phtml += '<button onclick="' + clickNext + '" ' + (nextDisabled ? 'disabled' : '') + ' class="btn btn-sm btn-outline" ' + (nextDisabled ? 'style="opacity:0.5;cursor:not-allowed"' : '') + '>&raquo;</button>';
        pagDiv.innerHTML = phtml;
    } else {
        pagDiv.innerHTML = '';
    }
}

loadLogStats();
loadLogs(1);
</script>
@endpush