<?php
class Role extends Model
{
    public function all(): array
    {
        return $this->db->query("SELECT * FROM roles ORDER BY id_role")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id_role = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
