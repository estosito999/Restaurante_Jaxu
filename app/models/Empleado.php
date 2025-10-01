<?php
namespace App\Models;

class Empleado extends BaseModel {

    /** Lista todos los empleados */
    public function all(): array {
        $st = $this->db->query("SELECT id_empleado,nombre,apellido,ci,puesto,rol FROM empleado ORDER BY rol DESC, nombre, apellido");
        return $st->fetchAll();
    }

    /** Busca por id */
    public function find(int $id): ?array {
        $st = $this->db->prepare("SELECT id_empleado,nombre,apellido,ci,puesto,rol FROM empleado WHERE id_empleado=?");
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    /** Crea empleado (hash de password si se provee) */
    public function create(array $data): int {
        $sql = "INSERT INTO empleado (nombre,apellido,ci,puesto,password_hash,rol)
                VALUES (?,?,?,?,?,?)";
        $st = $this->db->prepare($sql);
        $hash = !empty($data['password']) ? password_hash($data['password'], PASSWORD_BCRYPT) : null;
        $st->execute([
            $data['nombre'],
            $data['apellido'] ?: '',
            $data['ci'],
            $data['puesto'] ?: '',
            $hash,
            $data['rol'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    /** Actualiza datos (sin password) */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE empleado SET nombre=?, apellido=?, ci=?, puesto=?, rol=? WHERE id_empleado=?";
        $st = $this->db->prepare($sql);
        return $st->execute([
            $data['nombre'],
            $data['apellido'] ?: '',
            $data['ci'],
            $data['puesto'] ?: '',
            $data['rol'],
            $id
        ]);
    }

    /** Cambia contraseña */
    public function updatePassword(int $id, string $password): bool {
        $sql = "UPDATE empleado SET password_hash=? WHERE id_empleado=?";
        $st = $this->db->prepare($sql);
        return $st->execute([ password_hash($password, PASSWORD_BCRYPT), $id ]);
    }

    /** Borra empleado */
    public function delete(int $id): bool {
        $st = $this->db->prepare("DELETE FROM empleado WHERE id_empleado=?");
        return $st->execute([$id]);
    }

    /** Cuenta admins existentes */
    public function countAdmins(): int {
        $st = $this->db->query("SELECT COUNT(*) AS c FROM empleado WHERE rol='admin'");
        return (int)($st->fetch()['c'] ?? 0);
    }

    /** ¿Este id es admin? */
    public function isAdmin(int $id): bool {
        $st = $this->db->prepare("SELECT 1 FROM empleado WHERE id_empleado=? AND rol='admin' LIMIT 1");
        $st->execute([$id]);
        return (bool)$st->fetch();
    }
}
