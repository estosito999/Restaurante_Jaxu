<?php
namespace App\Models;

class PlatoBebidas extends BaseModel {

    /** Catálogo de categorías permitido */
    public function categorias(): array {
        // Puedes modificar según las categorías de tu restaurante
        return ['Almuerzo','Platos Especiales','Bebida','Postre'];
    }

    public function categoriaValida(string $cat): bool {
        return in_array($cat, $this->categorias(), true);
    }

    /** Listar (con filtro opcional por texto y categoría) */
    public function all(string $q = '', string $cat = ''): array {
        $sql = "SELECT p.*, e.nombre AS cocinero_nombre, e.apellido AS cocinero_apellido
                FROM plato_bebidas p
                LEFT JOIN empleado e ON e.id_empleado = p.id_cocinero
                WHERE 1=1";
        $args = [];
        if ($q !== '') {
            $sql .= " AND (p.nombre LIKE ?)";
            $args[] = "%$q%";
        }
        if ($cat !== '' && $this->categoriaValida($cat)) {
            $sql .= " AND p.categoria = ?";
            $args[] = $cat;
        }
        $sql .= " ORDER BY p.categoria, p.nombre";
        $st = $this->db->prepare($sql);
        $st->execute($args);
        return $st->fetchAll();
    }

    public function find(int $id): ?array {
        $st = $this->db->prepare("SELECT * FROM plato_bebidas WHERE id_plato = ?");
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function create(array $data): int {
        $st = $this->db->prepare("INSERT INTO plato_bebidas(nombre, precio, stock, categoria, id_cocinero)
                                  VALUES(?,?,?,?,?)");
        $st->execute([
            $data['nombre'],
            $data['precio'],
            $data['stock'],
            $data['categoria'],
            $data['id_cocinero']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $st = $this->db->prepare("UPDATE plato_bebidas 
                                  SET nombre=?, precio=?, stock=?, categoria=?, id_cocinero=? 
                                  WHERE id_plato=?");
        return $st->execute([
            $data['nombre'],
            $data['precio'],
            $data['stock'],
            $data['categoria'],
            $data['id_cocinero'],
            $id
        ]);
    }

    public function delete(int $id): bool {
        $st = $this->db->prepare("DELETE FROM plato_bebidas WHERE id_plato = ?");
        return $st->execute([$id]);
    }

    /** Validaciones/ayudas */
    public function empleadoExiste(int $id): bool {
        $st = $this->db->prepare("SELECT 1 FROM empleado WHERE id_empleado=? LIMIT 1");
        $st->execute([$id]);
        return (bool)$st->fetchColumn();
    }

    public function listarCocineros(): array {
        $st = $this->db->prepare("SELECT id_empleado, nombre, apellido 
                                  FROM empleado 
                                  WHERE puesto LIKE 'cocin%' 
                                  ORDER BY nombre, apellido");
        $st->execute();
        $rows = $st->fetchAll();

        if (!$rows) {
            $rows = $this->db->query("SELECT id_empleado, nombre, apellido 
                                      FROM empleado 
                                      ORDER BY nombre, apellido")->fetchAll();
        }
        return $rows;
    }
}
