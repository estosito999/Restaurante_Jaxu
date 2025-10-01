<h1>Crear Plato</h1>
<form method="POST" action="/platos">
  <input type="hidden" name="_csrf" value="<?= \Core\Session::getCsrf() ?>">
  <label>Nombre: <input name="nombre" required></label><br>
  <label>Descripción: <input name="descripcion"></label><br>
  <label>Precio: <input name="precio" type="number" step="0.01" required></label><br>
  <label>Categoría:
    <select name="categoria">
      <option>Almuerzo</option>
      <option>Especial</option>
      <option>Bebida</option>
    </select>
  </label><br>
  <label>ID Cocinero (empleado): <input name="id_cocinero" type="number" value="1" required></label><br>
  <button type="submit">Guardar</button>
</form>
