<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Acceso Empleados - Restaurante Jaxu</title>
  <style>
    /* ===== Base ===== */
    * { box-sizing: border-box; margin: 0; padding: 0; }
    :root{
      --grad-a:#667eea; --grad-b:#764ba2;
      --text:#2e2e2e; --muted:#666;
      --card:#fff; --bd:#e1e5e9;
      --ok:#2e7d32; --ok-bg:#e8f5e9;
      --err:#c62828; --err-bg:#ffebee;
      --radius:16px;
    }
    body{
      min-height:100vh; display:flex; align-items:center; justify-content:center;
      background: linear-gradient(135deg, var(--grad-a), var(--grad-b));
      padding:24px; font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", Ubuntu, "Cantarell", "DejaVu Sans", "Fira Sans", "Droid Sans", sans-serif;
      color:var(--text);
    }

    /* ===== Card ===== */
    .login-container{
      width:100%; max-width:480px; background:var(--card);
      border-radius:var(--radius);
      box-shadow:0 15px 35px rgba(0,0,0,.12);
      padding:36px 28px;
    }
    .logo{ text-align:center; margin-bottom:20px; }
    .logo h1{ font-size:26px; font-weight:700; line-height:1.1; }
    .logo small{ color:var(--muted); display:block; margin-top:6px; }

    /* ===== Flash ===== */
    .flash{ border-left:4px solid; padding:12px 14px; border-radius:10px; font-size:14px; margin:14px 0 18px; }
    .flash.error{ background:var(--err-bg); color:var(--err); border-left-color:var(--err); }
    .flash.ok{ background:var(--ok-bg); color:var(--ok); border-left-color:var(--ok); }

    /* ===== Form ===== */
    .form-group{ margin:16px 0; }
    label{ display:block; font-size:14px; font-weight:600; margin-bottom:8px; }
    .control{
      position:relative; display:flex; align-items:center;
      border:2px solid var(--bd); border-radius:10px; transition:.2s border-color, .2s box-shadow, .2s transform;
      background:#fff;
    }
    .control:focus-within{ border-color:var(--grad-a); box-shadow:0 0 0 3px rgba(102,126,234,.15); transform:translateY(-1px); }
    .control .icon{
      width:42px; display:grid; place-items:center; font-size:18px; opacity:.8; user-select:none;
    }
    .control input{
      flex:1; border:0; padding:12px 14px 12px 0; font-size:16px; background:transparent; outline:none;
    }
    .toggle-pass{
      position:absolute; right:8px; top:50%; transform:translateY(-50%);
      border:0; background:transparent; cursor:pointer; padding:6px 8px; font-size:13px; color:var(--muted);
    }

    .btn{
      width:100%; margin-top:8px; border:0; cursor:pointer;
      padding:14px 16px; border-radius:10px; font-size:16px; font-weight:700; color:#fff;
      background:linear-gradient(135deg, var(--grad-a), var(--grad-b));
      transition:.2s transform, .2s box-shadow, opacity .2s;
    }
    .btn:hover{ transform:translateY(-2px); box-shadow:0 10px 18px rgba(102,126,234,.35); }
    .btn:disabled{ opacity:.6; cursor:not-allowed; transform:none; box-shadow:none; }

    .help{ margin-top:16px; text-align:center; color:var(--muted); font-size:14px; }

    @media (max-width:480px){
      .login-container{ padding:28px 18px; }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="logo" aria-label="Restaurante Jaxu">
      <h1>🍽️ Restaurante Jaxu</h1>
      <small>Acceso para empleados</small>
    </div>

    <!-- Mensajes de servidor -->
    <?php if (!empty($flash)): ?>
      <div class="flash <?= (mb_stripos($flash, 'éxito') !== false || mb_stripos($flash, 'exito') !== false) ? 'ok' : 'error' ?>"
           role="status" aria-live="polite">
        <?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <form id="loginForm" method="POST" action="/../Restaurant_Jaxu/app/views/auth/login.php" novalidate>
      <input type="hidden" name="_csrf" value="<?= \Core\Session::getCsrf() ?>">

      <div class="form-group">
        <label for="ci">Carnet de Identidad</label>
        <div class="control">
          <span class="icon" aria-hidden="true">👤</span>
          <input
            id="ci" name="ci" inputmode="numeric" pattern="^\d{6,20}$"
            maxlength="20" required autocomplete="username"
            placeholder="Ej: 12345678" />
        </div>
      </div>

      <div class="form-group">
        <label for="password">Contraseña</label>
        <div class="control">
          <span class="icon" aria-hidden="true">🔒</span>
          <input
            id="password" name="password" type="password" required
            minlength="4" autocomplete="current-password"
            placeholder="Ingresa tu contraseña" />
          <button class="toggle-pass" type="button" aria-controls="password" aria-label="Mostrar u ocultar contraseña">Mostrar</button>
        </div>
      </div>

      <button class="btn" id="submitBtn" type="submit">🎯 Ingresar al Sistema</button>
    </form>

    <p class="help">¿Problemas para acceder? Contacta al administrador.</p>
  </div>

  <script>
    const form = document.getElementById('loginForm');
    const ci = document.getElementById('ci');
    const password = document.getElementById('password');
    const btn = document.getElementById('submitBtn');
    const toggle = document.querySelector('.toggle-pass');

    // Mostrar/ocultar contraseña
    toggle.addEventListener('click', () => {
      const visible = password.type === 'text';
      password.type = visible ? 'password' : 'text';
      toggle.textContent = visible ? 'Mostrar' : 'Ocultar';
      password.focus();
    });

    // Permite solo dígitos en CI al tipear/pegar
    ci.addEventListener('input', () => {
      ci.value = ci.value.replace(/\D+/g, '').slice(0, 20);
    });

    // Validación y prevención de doble submit
    form.addEventListener('submit', (e) => {
      const ciVal = ci.value.trim();
      const passVal = password.value;

      if (!ciVal || !passVal) {
        e.preventDefault();
        alert('Por favor, completa todos los campos.');
        return;
      }
      if (!/^\d{6,20}$/.test(ciVal)) {
        e.preventDefault();
        alert('El Carnet de Identidad debe contener solo números (6 a 20 dígitos).');
        ci.focus();
        return;
      }

      // Bloquea múltiples envíos
      btn.disabled = true;
      btn.textContent = 'Procesando…';
    });
  </script>
</body>
</html>
