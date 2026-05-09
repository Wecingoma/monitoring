<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MonitorIA - Connexion</title>
    @vite(['resources/css/app.css'])
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 oklch(0.55 0.2 260 / 0.4); }
            70% { box-shadow: 0 0 0 12px oklch(0.55 0.2 260 / 0); }
            100% { box-shadow: 0 0 0 0 oklch(0.55 0.2 260 / 0); }
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .login-bg {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background-color: var(--color-background);
            position: relative;
        }
        .login-bg::before {
            content: '';
            position: absolute;
            top: -40%;
            left: -20%;
            width: 60%;
            height: 80%;
            background: radial-gradient(ellipse, oklch(0.55 0.2 260 / 0.06), transparent 60%);
            pointer-events: none;
        }
        .login-bg::after {
            content: '';
            position: absolute;
            bottom: -40%;
            right: -20%;
            width: 60%;
            height: 80%;
            background: radial-gradient(ellipse, oklch(0.55 0.2 290 / 0.04), transparent 60%);
            pointer-events: none;
        }
        .login-card {
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            position: relative;
            z-index: 1;
        }
        .spinner {
            animation: spin 0.7s linear infinite;
        }
        .remember-checkbox {
            width: 1rem;
            height: 1rem;
            border-radius: var(--radius-sm);
            background: var(--color-background);
            border: 1px solid var(--color-input);
            appearance: none;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }
        .remember-checkbox:checked {
            background: var(--color-primary);
            border-color: var(--color-primary);
        }
        .remember-checkbox:checked::after {
            content: '';
            display: block;
            width: 0.3rem;
            height: 0.55rem;
            margin: 0.1rem 0 0 0.25rem;
            border: solid var(--color-primary-foreground);
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        .remember-checkbox:focus-visible {
            box-shadow: 0 0 0 2px var(--color-background), 0 0 0 4px var(--color-ring);
        }
    </style>
</head>
<body>
    <div id="toast-container" class="toast-container"></div>

    <div class="login-bg">
        <div class="login-card w-full max-w-md">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl mb-4" style="background:linear-gradient(135deg, oklch(0.55 0.2 260), oklch(0.55 0.2 290));">
                    <svg class="w-7 h-7 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--color-primary-foreground)"><rect x="3" y="12" width="4" height="9" rx="1"/><rect x="10" y="8" width="4" height="13" rx="1"/><rect x="17" y="4" width="4" height="17" rx="1"/></svg>
                </div>
                <h1 class="text-2xl font-bold tracking-tight" style="color:var(--color-foreground)">MonitorIA</h1>
                <p class="text-sm mt-1" style="color:var(--color-muted-foreground)">Système intelligent de monitoring</p>
            </div>

            <div class="card">
                <form id="loginForm" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color:var(--color-foreground)">Adresse email</label>
                        <input type="email" name="email" id="email" required autocomplete="email"
                            class="input" placeholder="votre@email.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color:var(--color-foreground)">Mot de passe</label>
                        <input type="password" name="password" id="password" required autocomplete="current-password"
                            class="input" placeholder="••••••••">
                    </div>
                    <div class="flex items-center gap-2.5">
                        <input type="checkbox" name="remember" id="remember" class="remember-checkbox">
                        <label for="remember" class="text-sm cursor-pointer" style="color:var(--color-muted-foreground)">Se souvenir de moi</label>
                    </div>
                    <button type="submit" id="submitBtn" class="btn btn-primary w-full" style="height:2.5rem">
                        <span id="btnText">Se connecter</span>
                        <svg id="btnSpinner" class="spinner w-4 h-4 hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </button>
                </form>

                <div class="separator mt-5"></div>
                <p class="text-center text-sm mt-4" style="color:var(--color-muted-foreground)">
                    Pas de compte ?
                    <a href="{{ route('register') }}" class="font-medium hover:underline" style="color:var(--color-primary)">Créer un compte</a>
                </p>
            </div>

            <p class="text-center text-xs mt-6" style="color:var(--color-muted-foreground)">&copy; 2026 MonitorIA. Tous droits réservés.</p>
        </div>
    </div>

    <script>
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

    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        var btn = document.getElementById('submitBtn');
        var btnText = document.getElementById('btnText');
        var spinner = document.getElementById('btnSpinner');
        var email = document.getElementById('email').value.trim();
        var password = document.getElementById('password').value;

        btn.disabled = true;
        btnText.textContent = 'Connexion...';
        spinner.classList.remove('hidden');

        try {
            var response = await fetch('/api/v1/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ email: email, password: password })
            });

            var data = await response.json();

            if (response.ok) {
                localStorage.setItem('token', data.token || data.access_token);
                localStorage.setItem('user', JSON.stringify(data.user));
                showToast('Connexion réussie', 'Bienvenue !', 'success');
                setTimeout(function() { window.location.href = '/dashboard'; }, 800);
            } else {
                var msg = data.message || data.error || 'Identifiants incorrects';
                showToast('Erreur de connexion', msg, 'error');
            }
        } catch (err) {
            showToast('Erreur de connexion', 'Impossible de contacter le serveur', 'error');
        } finally {
            btn.disabled = false;
            btnText.textContent = 'Se connecter';
            spinner.classList.add('hidden');
        }
    });
    </script>
</body>
</html>