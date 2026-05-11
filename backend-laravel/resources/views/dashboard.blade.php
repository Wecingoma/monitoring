@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', "Vue d'ensemble du système")

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="statsCards">

        <div class="card stat-card stat-card-blue animate-in">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Serveurs</p>
                    <svg class="w-5 h-5 text-primary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><circle cx="6" cy="6" r="1" fill="currentColor"/><circle cx="6" cy="18" r="1" fill="currentColor"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-foreground" id="totalServers">--</p>
            <p class="text-xs text-muted-foreground mt-1"><span class="text-success" id="onlineServers">0</span> en ligne / <span class="text-destructive" id="offlineServers">0</span> hors ligne</p>
        </div>

        <div class="card stat-card stat-card-red animate-in" style="animation-delay:0.05s">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Alertes critiques</p>
                    <svg class="w-5 h-5 text-destructive" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-foreground" id="criticalAlerts">--</p>
            <p class="text-xs text-muted-foreground mt-1"><span id="warningAlerts">0</span> alertes warning</p>
        </div>

        <div class="card stat-card stat-card-amber animate-in" style="animation-delay:0.1s">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Anomalies IA</p>
                    <svg class="w-5 h-5 text-warning" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18H10a3.374 3.374 0 01-.429-1.547l-.548-.547z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-foreground" id="totalAnomalies">--</p>
            <p class="text-xs text-muted-foreground mt-1"><span id="criticalAnomalies">0</span> critiques</p>
        </div>

        <div class="card stat-card stat-card-purple animate-in" style="animation-delay:0.15s">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Incidents</p>
                    <svg class="w-5 h-5 text-primary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:oklch(0.55 0.2 290)"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-foreground" id="openIncidents">--</p>
            <p class="text-xs text-muted-foreground mt-1 flex items-center gap-1.5">En cours <span class="inline-block w-2 h-2 rounded-full bg-green-500" style="animation:pulse-dot 2s ease-in-out infinite"></span></p>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="card lg:col-span-2">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <p class="card-title">Tendance CPU / RAM</p>
                    <div class="flex items-center gap-4 text-xs text-muted-foreground">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm" style="background:#3b82f6"></span>CPU</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm" style="background:#10b981"></span>RAM</span>
                    </div>
                </div>
            </div>
            <div id="metricsChart" style="height:280px"></div>
        </div>
        <div class="card">
            <div class="card-header">
                <p class="card-title">Alertes par sévérité</p>
            </div>
            <div id="alertsChart" style="height:280px"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <p class="card-title">Utilisation Disque</p>
                    <div class="flex items-center gap-4 text-xs text-muted-foreground">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm" style="background:#f59e0b"></span>Disque %</span>
                    </div>
                </div>
            </div>
            <div id="diskChart" style="height:220px"></div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <p class="card-title">Trafic Réseau</p>
                    <div class="flex items-center gap-4 text-xs text-muted-foreground">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm" style="background:#8b5cf6"></span>Mbps</span>
                    </div>
                </div>
            </div>
            <div id="networkChart" style="height:220px"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <div class="card">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <p class="card-title">Hôtes</p>
                    <a href="/servers" class="text-xs text-muted-foreground hover:text-foreground transition-colors">Voir tout &rarr;</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Serveur</th>
                            <th>Statut</th>
                            <th>CPU</th>
                            <th>RAM</th>
                            <th>Disque</th>
                            <th>Réseau</th>
                        </tr>
                    </thead>
                    <tbody id="serversTable">
                        <tr><td colspan="6" class="text-center text-muted-foreground py-8">Chargement...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <p class="card-title">Alertes récentes</p>
                    <a href="/alerts" class="text-xs text-muted-foreground hover:text-foreground transition-colors">Voir tout &rarr;</a>
                </div>
            </div>
            <div id="recentAlerts" class="space-y-2 max-h-96 overflow-y-auto">
                <p class="text-muted-foreground text-sm text-center py-8">Chargement...</p>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <div class="card">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <p class="card-title">Anomalies IA détectées</p>
                    <a href="/anomalies" class="text-xs text-muted-foreground hover:text-foreground transition-colors">Voir tout &rarr;</a>
                </div>
            </div>
            <div id="recentAnomalies" class="space-y-2 max-h-96 overflow-y-auto">
                <p class="text-muted-foreground text-sm text-center py-8">Chargement...</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <p class="card-title">Logs récents</p>
                    <a href="/logs" class="text-xs text-muted-foreground hover:text-foreground transition-colors">Voir tout &rarr;</a>
                </div>
            </div>
            <div id="recentLogs" class="space-y-1 max-h-80 overflow-y-auto">
                <p class="text-muted-foreground text-sm text-center py-8">Chargement...</p>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<style>
    @keyframes pulse-dot { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
</style>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
var metricsChartInstance = null;
var alertsChartInstance = null;
var diskChartInstance = null;
var networkChartInstance = null;
var REFRESH_INTERVAL = 15000;
var lastAlertCheck = new Date().toISOString();
var knownAlertIds = [];

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

function getProgressBar(value, defaultColor) {
    var colorClass;
    if (value > 90) colorClass = 'progress-red';
    else if (value > 75) colorClass = 'progress-amber';
    else if (value < 10) colorClass = 'progress-green';
    else colorClass = defaultColor || 'progress-blue';
    return '<div class="flex items-center gap-2">' +
        '<div class="progress ' + colorClass + '" style="flex:1;min-width:60px">' +
            '<div class="progress-bar" style="width:' + value + '%"></div>' +
        '</div>' +
        '<span class="text-xs text-muted-foreground font-mono w-8">' + value + '%</span>' +
    '</div>';
}

function getSeverityDot(severity) {
    var map = {
        critical: { color: 'oklch(0.55 0.2 25)', pulse: true },
        warning:  { color: 'oklch(0.70 0.18 65)', pulse: false },
        info:     { color: 'oklch(0.55 0.18 240)', pulse: false }
    };
    var s = map[severity] || map.info;
    var pulseStyle = s.pulse ? 'animation:pulse-ring 2s ease infinite;' : '';
    return '<span style="width:8px;height:8px;border-radius:9999px;background:' + s.color + ';flex-shrink:0;' + pulseStyle + 'display:inline-block"></span>';
}

function getSeverityBadge(severity) {
    var map = {
        critical: { cls: 'badge badge-destructive', label: 'Critique' },
        warning:  { cls: 'badge badge-warning', label: 'Warning' },
        info:     { cls: 'badge badge-info', label: 'Info' }
    };
    var s = map[severity] || map.info;
    return '<span class="' + s.cls + '">' + s.label + '</span>';
}

function getLevelBadge(level) {
    var map = {
        emergency: { cls: 'badge badge-destructive' },
        alert:     { cls: 'badge badge-destructive' },
        critical:  { cls: 'badge badge-destructive' },
        error:     { cls: 'badge badge-destructive' },
        warning:   { cls: 'badge badge-warning' },
        notice:    { cls: 'badge badge-info' },
        info:      { cls: 'badge badge-success' },
        debug:     { cls: 'badge badge-secondary' }
    };
    var s = map[level] || map.debug;
    return '<span class="' + s.cls + '">' + level.toUpperCase() + '</span>';
}

function typeBadge(type) {
    return '<span class="badge badge-info">' + (type || 'N/A') + '</span>';
}

function renderServers(servers) {
    var tbody = document.getElementById('serversTable');
    if (!servers || !servers.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-muted-foreground">Aucun serveur</td></tr>';
        return;
    }
    tbody.innerHTML = servers.slice(0, 8).map(function(s) {
        var netVal = s.network != null ? parseFloat(s.network).toFixed(2) : '0';
        return '<tr>' +
            '<td><a href="/servers/' + s.id + '" class="text-foreground hover:text-primary transition-colors font-medium">' + s.name + '</a></td>' +
            '<td>' + getStatusBadge(s.status) + '</td>' +
            '<td>' + getProgressBar(s.cpu, 'progress-blue') + '</td>' +
            '<td>' + getProgressBar(s.ram, 'progress-blue') + '</td>' +
            '<td>' + getProgressBar(s.disk, 'progress-blue') + '</td>' +
            '<td><span class="text-xs font-mono" style="color:var(--color-muted-foreground)">' + netVal + ' Mbps</span></td>' +
        '</tr>';
    }).join('');
}

function renderAlerts(alerts) {
    var el = document.getElementById('recentAlerts');
    if (!alerts || !alerts.length) {
        el.innerHTML = '<p class="text-muted-foreground text-sm text-center py-8">Aucune alerte</p>';
        return;
    }
    el.innerHTML = alerts.map(function(a) {
        return '<div class="flex items-start gap-3 p-3 rounded-lg hover:bg-accent/50 transition-colors border border-transparent hover:border-border">' +
            getSeverityDot(a.severity) +
            '<div class="flex-1 min-w-0">' +
                '<p class="text-sm text-foreground truncate">' + a.title + '</p>' +
                '<p class="text-xs text-muted-foreground mt-0.5">' + (a.server_name || 'Système') + ' &middot; ' + a.created_at + '</p>' +
            '</div>' +
            getSeverityBadge(a.severity) +
        '</div>';
    }).join('');
}

function renderAnomalies(anomalies) {
    var el = document.getElementById('recentAnomalies');
    if (!anomalies || !anomalies.length) {
        el.innerHTML = '<p class="text-muted-foreground text-sm text-center py-8">Aucune anomalie</p>';
        return;
    }
    el.innerHTML = anomalies.map(function(a) {
        var iconColor = a.severity === 'critical' ? 'text-destructive' : 'text-warning';
        return '<div class="flex items-start gap-3 p-3 rounded-lg hover:bg-accent/50 transition-colors border border-transparent hover:border-border">' +
            '<svg class="w-4 h-4 ' + iconColor + ' mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18H10a3.374 3.374 0 01-.429-1.547l-.548-.547z"/></svg>' +
            '<div class="flex-1 min-w-0">' +
                '<div class="flex items-center gap-2 mb-0.5">' +
                    typeBadge(a.type) +
                    '<span class="text-xs font-mono ' + iconColor + '">' + parseFloat(a.score).toFixed(2) + '</span>' +
                '</div>' +
                '<p class="text-sm text-foreground">' + a.description + '</p>' +
                '<p class="text-xs text-muted-foreground mt-0.5">' + a.server_name + ' &middot; ' + a.detected_at + '</p>' +
            '</div>' +
        '</div>';
    }).join('');
}

function renderLogs(logs) {
    var el = document.getElementById('recentLogs');
    if (!logs || !logs.length) {
        el.innerHTML = '<p class="text-muted-foreground text-sm text-center py-8">Aucun log</p>';
        return;
    }
    el.innerHTML = logs.map(function(l) {
        return '<div class="flex items-center gap-2 py-1.5">' +
            getLevelBadge(l.level) +
            '<span class="text-sm text-foreground flex-1 truncate">' + l.message + '</span>' +
            '<span class="text-xs text-muted-foreground font-mono whitespace-nowrap">' + (l.server_name || '') + ' &middot; ' + l.source + ' &middot; ' + l.logged_at + '</span>' +
        '</div>';
    }).join('');
}

function renderMetricsChart(cpuData, ramData) {
    var el = document.getElementById('metricsChart');
    var cpuSeries = Object.values(cpuData);
    var ramSeries = Object.values(ramData);
    var categories = Object.keys(cpuData).map(function(k) { return k.split(' ')[1] || k; });

    if (metricsChartInstance) {
        metricsChartInstance.updateOptions({
            xaxis: { categories: categories },
            series: [
                { name: 'CPU %', data: cpuSeries },
                { name: 'RAM %', data: ramSeries }
            ]
        });
        return;
    }

    var options = {
        chart: {
            type: 'area',
            background: 'transparent',
            foreColor: 'var(--color-muted-foreground)',
            fontFamily: 'inherit',
            toolbar: { show: false },
            animations: { enabled: true, easing: 'easeinout', speed: 600 }
        },
        series: [
            { name: 'CPU %', data: cpuSeries },
            { name: 'RAM %', data: ramSeries }
        ],
        xaxis: {
            categories: categories,
            labels: { style: { colors: 'var(--color-muted-foreground)', fontSize: '11px' } },
            axisBorder: { color: 'var(--color-border)' },
            axisTicks: { color: 'var(--color-border)' }
        },
        yaxis: {
            labels: {
                style: { colors: 'var(--color-muted-foreground)', fontSize: '11px' },
                formatter: function(v) { return Math.round(v) + '%'; }
            },
            max: 100,
            tickAmount: 5
        },
        colors: ['#3b82f6', '#10b981'],
        stroke: { curve: 'smooth', width: [2, 2] },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.35,
                opacityTo: 0.05,
                stops: [0, 95, 100]
            }
        },
        grid: {
            borderColor: 'var(--color-border)',
            strokeDashArray: 4,
            xaxis: { lines: { show: false } }
        },
        tooltip: {
            theme: 'dark',
            y: { formatter: function(v) { return v + '%'; } }
        },
        legend: { show: false },
        markers: { size: 0, hover: { size: 5, sizeOffset: 3 } }
    };
    metricsChartInstance = new ApexCharts(el, options);
    metricsChartInstance.render();
}

function renderDiskChart(diskData) {
    var el = document.getElementById('diskChart');
    var series = Object.values(diskData);
    var categories = Object.keys(diskData).map(function(k) { return k.split(' ')[1] || k; });

    if (diskChartInstance) {
        diskChartInstance.updateOptions({
            xaxis: { categories: categories },
            series: [{ name: 'Disque %', data: series }]
        });
        return;
    }

    var options = {
        chart: {
            type: 'area',
            background: 'transparent',
            foreColor: 'var(--color-muted-foreground)',
            fontFamily: 'inherit',
            toolbar: { show: false },
            animations: { enabled: true, easing: 'easeinout', speed: 600 }
        },
        series: [{ name: 'Disque %', data: series }],
        xaxis: {
            categories: categories,
            labels: { style: { colors: 'var(--color-muted-foreground)', fontSize: '11px' } },
            axisBorder: { color: 'var(--color-border)' },
            axisTicks: { color: 'var(--color-border)' }
        },
        yaxis: {
            labels: {
                style: { colors: 'var(--color-muted-foreground)', fontSize: '11px' },
                formatter: function(v) { return Math.round(v) + '%'; }
            },
            max: 100,
            tickAmount: 5
        },
        colors: ['#f59e0b'],
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 95, 100] } },
        grid: { borderColor: 'var(--color-border)', strokeDashArray: 4, xaxis: { lines: { show: false } } },
        tooltip: { theme: 'dark', y: { formatter: function(v) { return v + '%'; } } },
        legend: { show: false },
        markers: { size: 0, hover: { size: 5, sizeOffset: 3 } }
    };
    diskChartInstance = new ApexCharts(el, options);
    diskChartInstance.render();
}

function renderNetworkChart(netData) {
    var el = document.getElementById('networkChart');
    var series = Object.values(netData);
    var categories = Object.keys(netData).map(function(k) { return k.split(' ')[1] || k; });

    if (networkChartInstance) {
        networkChartInstance.updateOptions({
            xaxis: { categories: categories },
            series: [{ name: 'Réseau Mbps', data: series }]
        });
        return;
    }

    var options = {
        chart: {
            type: 'area',
            background: 'transparent',
            foreColor: 'var(--color-muted-foreground)',
            fontFamily: 'inherit',
            toolbar: { show: false },
            animations: { enabled: true, easing: 'easeinout', speed: 600 }
        },
        series: [{ name: 'Réseau Mbps', data: series }],
        xaxis: {
            categories: categories,
            labels: { style: { colors: 'var(--color-muted-foreground)', fontSize: '11px' } },
            axisBorder: { color: 'var(--color-border)' },
            axisTicks: { color: 'var(--color-border)' }
        },
        yaxis: {
            labels: {
                style: { colors: 'var(--color-muted-foreground)', fontSize: '11px' },
                formatter: function(v) { return parseFloat(v).toFixed(2) + ' Mbps'; }
            },
            tickAmount: 4
        },
        colors: ['#8b5cf6'],
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 95, 100] } },
        grid: { borderColor: 'var(--color-border)', strokeDashArray: 4, xaxis: { lines: { show: false } } },
        tooltip: { theme: 'dark', y: { formatter: function(v) { return parseFloat(v).toFixed(3) + ' Mbps'; } } },
        legend: { show: false },
        markers: { size: 0, hover: { size: 5, sizeOffset: 3 } }
    };
    networkChartInstance = new ApexCharts(el, options);
    networkChartInstance.render();
}

function renderAlertsChart(chartData) {
    var el = document.getElementById('alertsChart');
    if (alertsChartInstance) alertsChartInstance.destroy();
    var series = [chartData.critical || 0, chartData.warning || 0, chartData.info || 0];
    var options = {
        chart: {
            type: 'donut',
            background: 'transparent',
            foreColor: 'var(--color-muted-foreground)',
            fontFamily: 'inherit'
        },
        series: series,
        labels: ['Critique', 'Warning', 'Info'],
        colors: ['#ef4444', '#f59e0b', '#3b82f6'],
        plotOptions: {
            pie: {
                donut: {
                    size: '72%',
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            color: 'var(--color-muted-foreground)',
                            fontFamily: 'inherit',
                            fontSize: '12px'
                        },
                        value: {
                            show: true,
                            color: 'var(--color-foreground)',
                            fontFamily: 'inherit',
                            fontSize: '20px',
                            fontWeight: 600
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            color: 'var(--color-muted-foreground)',
                            formatter: function(w) {
                                return w.globals.seriesTotals.reduce(function(a, b) { return a + b; }, 0);
                            }
                        }
                    }
                }
            }
        },
        legend: {
            position: 'bottom',
            fontSize: '12px',
            labels: { colors: 'var(--color-muted-foreground)' },
            markers: { width: 10, height: 10, radius: 2 }
        },
        dataLabels: { enabled: false },
        stroke: { width: 2, colors: ['var(--color-card)'] }
    };
    alertsChartInstance = new ApexCharts(el, options);
    alertsChartInstance.render();
}

async function loadDashboard() {
    try {
        var data = await apiCall('/api/v1/dashboard');

        document.getElementById('totalServers').textContent = data.stats.total_servers;
        document.getElementById('onlineServers').textContent = data.stats.online_servers;
        document.getElementById('offlineServers').textContent = data.stats.offline_servers;
        document.getElementById('criticalAlerts').textContent = data.stats.critical_alerts;
        document.getElementById('warningAlerts').textContent = data.stats.warning_alerts;
        document.getElementById('totalAnomalies').textContent = data.stats.total_anomalies;
        document.getElementById('criticalAnomalies').textContent = data.stats.critical_anomalies;
        document.getElementById('openIncidents').textContent = data.stats.open_incidents;

        renderServers(data.metrics.servers || []);
        renderAlerts(data.recent_alerts || []);
        renderAnomalies(data.recent_anomalies || []);
        renderLogs(data.recent_logs || []);
        renderAlertsChart(data.alerts_chart || {});
        renderMetricsChart(data.cpu_trend || {}, data.ram_trend || {});
        renderDiskChart(data.disk_trend || {});
        renderNetworkChart(data.network_trend || {});
    } catch (e) {
        console.error('Erreur chargement dashboard:', e);
    }
}

async function pollNewAlerts() {
    try {
        var alerts = await apiCall('/api/v1/alerts/new?since=' + encodeURIComponent(lastAlertCheck));
        if (alerts && alerts.length > 0) {
            alerts.forEach(function(a) {
                if (knownAlertIds.indexOf(a.id) === -1) {
                    knownAlertIds.push(a.id);
                    var icon = a.severity === 'critical' ? '🔴' : (a.severity === 'warning' ? '🟡' : '🔵');
                    var serverInfo = a.server_name ? ' [' + a.server_name + ']' : '';
                    showToast(
                        icon + ' Alerte ' + a.severity,
                        a.title + serverInfo,
                        a.severity === 'critical' ? 'error' : (a.severity === 'warning' ? 'warning' : 'info')
                    );
                }
            });
        }
        lastAlertCheck = new Date().toISOString();
    } catch (e) {}
}

loadDashboard();
setInterval(loadDashboard, REFRESH_INTERVAL);
setInterval(pollNewAlerts, 10000);
</script>
@endpush