<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Acceso Empleados - Restaurante Jaxu</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    :root{
      --grad-a:#fd2f00; --grad-b:#6ea834;
      --text:#2e2e2e; --muted:#666; --card:#fff; --bd:#e1e5e9;
      --ok:#2e7d32; --ok-bg:#e8f5e9; --err:#c62828; --err-bg:#ffebee; --radius:16px;
    }
    body{
      min-height:100vh; display:flex; align-items:center; justify-content:center;
      background:linear-gradient(135deg,var(--grad-a),var(--grad-b));
      padding:24px; font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif; color:var(--text);
    }
    .login-container{ width:100%; max-width:520px; background:var(--card); border-radius:var(--radius);
      box-shadow:0 15px 35px rgba(46,44,44,.12); padding:28px 22px; }
    .logo{ text-align:center; margin-bottom:10px; }
    .logo h1{ font-size:26px; font-weight:700; line-height:1.1; }
    .logo small{ color:var(--muted); display:block; margin-top:6px; }

    .flash{ border-left:4px solid; padding:12px 14px; border-radius:10px; font-size:14px; margin:14px 0 18px; }
    .flash.error{ background:var(--err-bg); color:var(--err); border-left-color:var(--err); }
    .flash.ok{ background:var(--ok-bg); color:var(--ok); border-left-color:var(--ok); }

    .links{ display:flex; gap:12px; justify-content:space-between; flex-wrap:wrap; margin:10px 0 4px; }
    .link{ color:#0d47a1; text-decoration:none; font-weight:700; }
    .link:hover{ text-decoration:underline; }

    .form{ margin-top:6px; }
    .form-group{ margin:14px 0; }
    label{ display:block; font-size:14px; font-weight:600; margin-bottom:8px; }
    .control{ position:relative; display:flex; align-items:center; border:2px solid var(--bd); border-radius:10px; background:#fff; transition:.2s; }
    .control:focus-within{ border-color:var(--grad-a); box-shadow:0 0 0 3px rgba(253,47,0,.12); transform:translateY(-1px); }
    .icon{ width:42px; display:grid; place-items:center; font-size:18px; opacity:.8; user-select:none; }
    .control input,.control select{ flex:1; border:0; padding:12px 14px 12px 0; font-size:16px; background:transparent; outline:none; }
    .toggle-pass{ position:absolute; right:8px; top:50%; transform:translateY(-50%);
      border:0; background:transparent; cursor:pointer; padding:6px 8px; font-size:13px; color:var(--muted); }

    .btn{ width:100%; margin-top:8px; border:0; cursor:pointer; padding:14px 16px; border-radius:10px;
      font-size:16px; font-weight:700; color:#fff; background:linear-gradient(135deg,var(--grad-a),var(--grad-b));
      transition:.2s transform,.2s box-shadow,opacity .2s; }
    .btn:hover{ transform:translateY(-2px); box-shadow:0 10px 18px rgba(102,126,234,.35); }
    .btn:disabled{ opacity:.6; cursor:not-allowed; transform:none; box-shadow:none; }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="logo" aria-label="Restaurante Jaxu">
      <h1>🍽️ Restaurante Jaxu</h1>
      <small>Acceso para empleados</small>
    </div>

    <?php if (!empty($flash)): ?>
      <?php
        // sin mbstring
        $isOk = (stripos($flash, 'éxito') !== false) || (stripos($flash, 'exito') !== false);
      ?>
      <div class="flash <?= $isOk ? 'ok' : 'error' ?>" role="status" aria-live="polite">
        <?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>


    <!-- Enlaces a otras vistas -->
    <?php
    $allowSignup = false;
    try {
        if (isset($pdo) && $pdo instanceof PDO) {
            $allowSignup = !$pdo->query("SELECT 1 FROM empleado LIMIT 1")->fetchColumn();
        }
    } catch (Exception $e) {
        $allowSignup = false;
    }
    ?>

    <div class="links">
        <?php if ($allowSignup): ?>
            <a href="<?= BASE_URI ?>/signup" class="link">📝 Registrarse</a>
        <?php endif; ?>
        <a href="<?= BASE_URI ?>/forgot-password" class="link">🔑 ¿Olvidaste tu contraseña?</a>
    </div>

    <!-- Form login -->
    <section class="form">
      <form id="loginForm" method="POST" action="<?= BASE_URI ?>/login" novalidate>
        <input type="hidden" name="_token" value="<?= \Core\Session::getCsrf() ?>">
        <div class="form-group">
          <label for="ci">Carnet de Identidad</label>
          <div class="control">
            <span class="icon" aria-hidden="true">👤</span>
            <input id="ci" name="ci" inputmode="numeric" pattern="^\d{6,20}$" maxlength="20" required
                   autocomplete="username" placeholder="Ej: 12345678" autofocus />
          </div>
        </div>
        <div class="form-group">
          <label for="password">Contraseña</label>
          <div class="control">
            <span class="icon" aria-hidden="true">🔒</span>
            <input id="password" name="password" type="password" required minlength="4"
                   autocomplete="current-password" placeholder="Ingresa tu contraseña" />
            <button class="toggle-pass" type="button" data-target="password"
                    aria-label="Mostrar u ocultar contraseña" aria-pressed="false">Mostrar</button>
          </div>
        </div>
        <button class="btn" id="submitLogin" type="submit">🎯 Ingresar al Sistema</button>
      </form>
    </section>
  </div>

  <script>
    // Toggle password
    document.addEventListener('click', (ev)=>{
      const b = ev.target.closest('.toggle-pass'); if(!b) return;
      const input = document.getElementById(b.getAttribute('data-target'));
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      b.textContent = show ? 'Ocultar' : 'Mostrar';
      b.setAttribute('aria-pressed', String(show));
      input.focus();
    });

    // Validación y bloqueo doble submit
    const form = document.getElementById('loginForm');
    const ci = document.getElementById('ci');
    const pass = document.getElementById('password');
    const btn = document.getElementById('submitLogin');

    ci.addEventListener('input', ()=> ci.value = ci.value.replace(/\D+/g,'').slice(0,20));

    form.addEventListener('submit', (e)=>{
      if (!/^\d{6,20}$/.test(ci.value) || !pass.value.trim()){
        e.preventDefault(); alert('Completa CI (6-20 dígitos) y contraseña.'); return;
      }
      btn.disabled = true; btn.textContent = 'Procesando…';
    });
  </script>
</body>
</html>