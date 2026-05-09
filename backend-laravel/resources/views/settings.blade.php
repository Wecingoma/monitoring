@extends('layouts.app')
@section('title', 'Paramètres')
@section('subtitle', 'Configuration et état du système')

@section('content')
<div class="max-w-4xl space-y-6">

    <div>
        <h3 class="card-title mb-3" style="font-size:1.0625rem">État des connexions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <div class="card">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background:oklch(0.55 0.2 260 / 0.12)">
                        <span class="text-lg font-bold" style="color:oklch(0.55 0.2 260)">Z</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-foreground">Zabbix Server</p>
                        <p class="text-xs text-muted-foreground">Collecte de métriques</p>
                    </div>
                </div>
                <div class="flex items-center gap-2" id="zabbixStatusRow">
                    <svg class="spinner w-4 h-4" style="color:var(--color-muted-foreground)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span class="text-xs text-muted-foreground">Vérification...</span>
                </div>
                <p id="zabbixVersion" class="text-xs text-muted-foreground mt-2" style="opacity:0.6"></p>
            </div>

            <div class="card">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background:oklch(0.55 0.2 240 / 0.12)">
                        <span class="text-lg font-bold" style="color:oklch(0.55 0.2 240)">E</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-foreground">Elasticsearch</p>
                        <p class="text-xs text-muted-foreground">Centralisation des logs</p>
                    </div>
                </div>
                <div class="flex items-center gap-2" id="elasticStatusRow">
                    <svg class="spinner w-4 h-4" style="color:var(--color-muted-foreground)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span class="text-xs text-muted-foreground">Vérification...</span>
                </div>
                <p id="elasticVersion" class="text-xs text-muted-foreground mt-2" style="opacity:0.6"></p>
            </div>

            <div class="card">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background:oklch(0.55 0.2 30 / 0.12)">
                        <span class="text-lg font-bold" style="color:oklch(0.55 0.2 30)">G</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-foreground">Grafana</p>
                        <p class="text-xs text-muted-foreground">Visualisation des métriques</p>
                    </div>
                </div>
                <div class="flex items-center gap-2" id="grafanaStatusRow">
                    <svg class="spinner w-4 h-4" style="color:var(--color-muted-foreground)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span class="text-xs text-muted-foreground">Vérification...</span>
                </div>
                <p id="grafanaVersion" class="text-xs text-muted-foreground mt-2" style="opacity:0.6"></p>
            </div>

            <div class="card">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background:oklch(0.55 0.2 290 / 0.12)">
                        <svg class="w-5 h-5" style="color:oklch(0.55 0.2 290)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18H10a3.374 3.374 0 01-.429-1.547l-.548-.547z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-foreground">Module IA</p>
                        <p class="text-xs text-muted-foreground">Détection d'anomalies</p>
                    </div>
                </div>
                <div class="flex items-center gap-2" id="aiStatusRow">
                    <svg class="spinner w-4 h-4" style="color:var(--color-muted-foreground)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span class="text-xs text-muted-foreground">Vérification...</span>
                </div>
                <p id="aiVersion" class="text-xs text-muted-foreground mt-2" style="opacity:0.6"></p>
            </div>

        </div>
    </div>

    <div>
        <h3 class="card-title mb-3" style="font-size:1.0625rem">Informations système</h3>
        <div class="card">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="flex items-center gap-3 p-3 rounded-lg" style="background:var(--color-background)">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:oklch(0.55 0.2 260 / 0.12)">
                        <svg class="w-4 h-4" style="color:oklch(0.55 0.2 260)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Laravel</p>
                        <p id="laravelVersion" class="text-sm text-foreground font-medium">---</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-lg" style="background:var(--color-background)">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:oklch(0.55 0.2 290 / 0.12)">
                        <svg class="w-4 h-4" style="color:oklch(0.55 0.2 290)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">PHP</p>
                        <p class="text-sm text-foreground font-medium"><?php echo phpversion(); ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-lg" style="background:var(--color-background)">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:oklch(0.55 0.2 145 / 0.12)">
                        <svg class="w-4 h-4" style="color:oklch(0.55 0.2 145)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Base de données</p>
                        <p id="dbStatus" class="text-sm text-foreground font-medium">PostgreSQL</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-lg" style="background:var(--color-background)">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:oklch(0.70 0.18 65 / 0.12)">
                        <svg class="w-4 h-4" style="color:oklch(0.70 0.18 65)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Environnement</p>
                        <p class="text-sm text-foreground font-medium">{{ app()->environment() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <h3 class="card-title mb-3" style="font-size:1.0625rem">Profil utilisateur</h3>
        <div class="card">
            <div class="flex items-center gap-4">
                <div id="profileAvatar" class="w-14 h-14 rounded-xl flex items-center justify-center text-xl font-bold text-white shadow-lg" style="background:linear-gradient(135deg, oklch(0.55 0.2 260), oklch(0.55 0.2 290));box-shadow:0 8px 20px oklch(0.55 0.2 260 / 0.2)">U</div>
                <div>
                    <p id="profileName" class="text-lg font-semibold text-foreground">---</p>
                    <p id="profileEmail" class="text-sm text-muted-foreground">---</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span id="profileRole" class="badge badge-info"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<style>
    @keyframes spin { to { transform: rotate(360deg); } }
    .spinner { animation: spin 0.7s linear infinite; }
</style>
<script>
function setConnectionStatus(containerId, versionId, ok, label, version) {
    var container = document.getElementById(containerId);
    var dotColor = ok ? 'oklch(0.55 0.2 145)' : 'oklch(0.55 0.2 25)';
    var textColor = ok ? 'color:oklch(0.55 0.2 145)' : 'color:oklch(0.55 0.2 25)';
    var text = label || (ok ? 'Connecté' : 'Déconnecté');

    container.innerHTML =
        '<span style="width:8px;height:8px;border-radius:9999px;background:' + dotColor + ';display:inline-block;flex-shrink:0"></span>' +
        '<span class="text-xs font-medium" style="' + textColor + '">' + text + '</span>';

    if (versionId && version) {
        document.getElementById(versionId).textContent = version;
    }
}

async function checkConnections() {
    try {
        var zRes = await apiCall('/api/v1/zabbix/availability');
        var zOk = zRes.available !== undefined ? zRes.available : (zRes.status === 'ok' || zRes.status === 'healthy');
        setConnectionStatus('zabbixStatusRow', 'zabbixVersion', zOk, zOk ? 'Connecté' : 'Déconnecté', zRes.version || '');
    } catch (e) {
        setConnectionStatus('zabbixStatusRow', 'zabbixVersion', false, 'Déconnecté');
        showToast('Zabbix', 'Impossible de joindre le serveur Zabbix', 'error');
    }

    try {
        var eRes = await apiCall('/api/v1/elastic/health');
        var eOk = eRes.available === true;
        var eVersion = eRes.version || '';
        setConnectionStatus('elasticStatusRow', 'elasticVersion', eOk, eOk ? 'Connecté' : 'Déconnecté', eVersion);
    } catch (e) {
        setConnectionStatus('elasticStatusRow', 'elasticVersion', false, 'Déconnecté');
        showToast('Elasticsearch', 'Impossible de joindre Elasticsearch', 'error');
    }

    try {
        var gRes = await apiCall('/api/v1/grafana/health');
        var gOk = gOk = gRes.database === 'ok' || gRes.available === true;
        var gVersion = gRes.version || '';
        setConnectionStatus('grafanaStatusRow', 'grafanaVersion', gOk, gOk ? 'Connecté' : 'Déconnecté', gVersion);
    } catch (e) {
        setConnectionStatus('grafanaStatusRow', 'grafanaVersion', false, 'Déconnecté');
        showToast('Grafana', 'Impossible de joindre Grafana', 'error');
    }

    try {
        var aData = await apiCall('/api/v1/ai/health');
        var aOk = aData.status === 'healthy' || aData.status === 'ok';
        setConnectionStatus('aiStatusRow', 'aiVersion', aOk, aOk ? 'Connecté' : 'Déconnecté', aData.version || '');
    } catch (e) {
        setConnectionStatus('aiStatusRow', 'aiVersion', false, 'Déconnecté');
        showToast('Module IA', 'Impossible de joindre le module IA', 'error');
    }
}

function loadProfile() {
    var user = null;
    try { user = JSON.parse(localStorage.getItem('user')); } catch(e) {}
    if (user && user.name) {
        var initials = user.name.split(' ').map(function(n) { return n.charAt(0); }).join('').toUpperCase().slice(0, 2);
        document.getElementById('profileAvatar').textContent = initials;
        document.getElementById('profileName').textContent = user.name;
        document.getElementById('profileEmail').textContent = user.email || '';
        document.getElementById('profileRole').textContent = user.role || '';
    }
}

try {
    document.getElementById('laravelVersion').textContent = 'v' + ({{ app()->version() }} ? '{{ app()->version() }}' : '');
} catch(e) {
    document.getElementById('laravelVersion').textContent = 'v11.x';
}

checkConnections();
loadProfile();
</script>
@endpush