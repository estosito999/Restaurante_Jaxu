<h1>Clientes</h1>

<?php if(!empty($flash)): ?>
  <p class="flash"><?= htmlspecialchars($flash) ?></p>
<?php endif; ?>

<div class="grid cols-2">
  <!-- Listado -->
  <section>
    <h3>Listado</h3>
    <div class="sep"></div>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Nombre</th>
          <th>NIT</th>
          <th>Teléfono</th>
          <th>Email</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($clientes as $c): ?>
        <tr>
          <td><?= (int)$c['id_cliente'] ?></td>
          <td><?= htmlspecialchars(trim(($c['nombre'] ?? '').' '.($c['apellido'] ?? ''))) ?></td>
          <td><?= htmlspecialchars($c['nit'] ?? '') ?></td>
          <td><?= htmlspecialchars($c['telefono'] ?? '') ?></td>
          <td><?= htmlspecialchars($c['email'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <!-- Alta rápida -->
  <section>
    <h3>Nuevo cliente</h3>
    <div class="sep"></div>
    <form method="POST" action="<?= BASE_URI ?>/clientes">
      <input type="hidden" name="_csrf" value="<?= \Core\Session::getCsrf() ?>">

      <label>Nombre *
        <input type="text" name="nombre" maxlength="100" required>
      </label><br>

      <label>Apellido
        <input type="text" name="apellido" maxlength="100">
      </label><br>

      <label>NIT / CI
        <input type="text" name="nit" maxlength="20">
      </label><br>

      <label>Teléfono
        <input type="text" name="telefono" maxlength="20">
      </label><br>

      <label>Email
        <input type="email" name="email" maxlength="100">
      </label><br><br>

      <button type="submit">Guardar</button>
    </form>
  </section>
</div>

<style>
  table { width:100%; border-collapse: collapse; }
  th, td { padding: .5rem .6rem; border-bottom: 1px solid #1f2937; text-align: left; }
  th { color:#9ca3af; font-weight:600; }
  input, select, button {
    background:#0b1220; color:#e5e7eb; border:1px solid #1f2937; border-radius:.5rem; padding:.45rem .6rem;
  }
  input:focus, select:focus { outline: 1px solid #334155; }
  button { cursor:pointer; }
  form label { display:block; margin-bottom:.6rem; }
</style>
