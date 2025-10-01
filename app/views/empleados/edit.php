<h1>Editar empleado #<?= (int)$emp['id_empleado'] ?></h1>

<form method="POST" action="<?= BASE_URI ?>/empleados/actualizar/<?= (int)$emp['id_empleado'] ?>">
  <input type="hidden" name="_csrf" value="<?= \Core\Session::getCsrf() ?>">

  <label>Nombre *<br><input name="nombre" required maxlength="50" value="<?= htmlspecialchars($emp['nombre']) ?>"></label><br>
  <label>Apellido<br><input name="apellido" maxlength="50" value="<?= htmlspecialchars($emp['apellido']) ?>"></label><br>
  <label>CI *<br><input name="ci" required maxlength="20" value="<?= htmlspecialchars($emp['ci']) ?>"></label><br>
  <label>Puesto<br><input name="puesto" maxlength="20" value="<?= htmlspecialchars($emp['puesto']) ?>"></label><br>

  <label>Rol *<br>
    <select name="rol" required>
      <?php foreach(['mesero','cajero','admin'] as $r): ?>
        <option value="<?= $r ?>" <?= $emp['rol']===$r ? 'selected' : '' ?>><?= $r ?></option>
      <?php endforeach; ?>
    </select>
  </label><br><br>

  <button type="submit">Actualizar</button>
  <a href="<?= BASE_URI ?>/empleados">Cancelar</a>
</form>
