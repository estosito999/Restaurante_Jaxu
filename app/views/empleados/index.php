<h1>Empleados</h1>
<?php if(!empty($flash)): ?><p class="flash"><?= htmlspecialchars($flash) ?></p><?php endif; ?>

<p>
  <a href="<?= BASE_URI ?>/empleados/crear">+ Nuevo empleado</a>
</p>

<table>
  <thead>
    <tr>
      <th>#</th><th>Nombre</th><th>CI</th><th>Puesto</th><th>Rol</th><th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($empleados as $e): ?>
    <tr>
      <td><?= (int)$e['id_empleado'] ?></td>
      <td><?= htmlspecialchars($e['nombre'].' '.$e['apellido']) ?></td>
      <td><?= htmlspecialchars($e['ci']) ?></td>
      <td><?= htmlspecialchars($e['puesto']) ?></td>
      <td><?= htmlspecialchars($e['rol']) ?></td>
      <td>
        <a href="<?= BASE_URI ?>/empleados/editar/<?= (int)$e['id_empleado'] ?>">Editar</a> |
        <a href="<?= BASE_URI ?>/empleados/password/<?= (int)$e['id_empleado'] ?>">Contraseña</a> |
        <form action="<?= BASE_URI ?>/empleados/eliminar/<?= (int)$e['id_empleado'] ?>" method="POST" style="display:inline">
          <input type="hidden" name="_csrf" value="<?= \Core\Session::getCsrf() ?>">
          <button type="submit" onclick="return confirm('¿Eliminar empleado?')">Eliminar</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<style>
  table { width:100%; border-collapse: collapse; }
  th, td { padding: .5rem .6rem; border-bottom: 1px solid #1f2937; text-align: left; }
  th { color:#9ca3af; font-weight:600; }
  input, select, button {
    background:#0b1220; color:#e5e7eb; border:1px solid #1f2937; border-radius:.5rem; padding:.45rem .6rem;
  }
  button { cursor:pointer; }
</style>
