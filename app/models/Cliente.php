<?php
namespace App\Models;

class Cliente extends BaseModel {

    public function all(): array {
        $st = $this->db->query("SELECT * FROM cliente ORDER BY nombre, apellido");
        return $st->fetchAll();
    }

    public function create(array $data): int {
        $st = $this->db->prepare(
            "INSERT INTO cliente (nombre, apellido, nit, telefono, email)
             VALUES (?, ?, ?, ?, ?)"
        );
        $st->execute([
            $data['nombre'],
            $data['apellido'] ?: null,
            $data['nit'] ?: null,
            $data['telefono'] ?: null,
            $data['email'] ?: null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /* Métodos opcionales por si luego haces CRUD completo:
    public function find(int $id): ?array {
        $st = $this->db->prepare("SELECT * FROM cliente WHERE id_cliente=?");
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function update(int $id, array $data): bool {
        $st = $this->db->prepare(
            "UPDATE cliente
             SET nombre=?, apellido=?, nit=?, telefono=?, email=?
             WHERE id_cliente=?"
        );
        return $st->execute([
            $data['nombre'],
            $data['apellido'] ?: null,
            $data['nit'] ?: null,
            $data['telefono'] ?: null,
            $data['email'] ?: null,
            $id
        ]);
    }

    public function delete(int $id): bool {
        $st = $this->db->prepare("DELETE FROM cliente WHERE id_cliente=?");
        return $st->execute([$id]);
    }
    */
}
